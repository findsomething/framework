<?php
namespace FSth\Framework\Tool;

use FSth\Framework\Tool\ArrayTool;

class CacheList
{
    private $cache;
    private $key;

    /**
     * CacheList constructor.
     * @param $key
     * @param $cache redis 
     */
    public function __construct($key, $cache)
    {
        $this->key = $key;
        $this->cache = $cache;
    }

    public function push($value)
    {
        return $this->cache->rPush($this->key, ArrayTool::toString($value));
    }

    public function bPop()
    {
        return ArrayTool::toArray($this->cache->blPop($this->key, 1));
    }

    public function get($num, $trim = true)
    {
        $len = $this->len();
        $num = min($len, $num);
        if (empty($num)) {
            return [];
        }
        $pipe = $this->cache->multi(\Redis::PIPELINE);
        for ($i = 0; $i < $num; $i++) {
            $pipe->lPop($this->key);
        }
        $values = $pipe->exec();
        return $values[0] !== false ? $values : [];
    }

    public function len()
    {
        $len = $this->cache->lLen($this->key);
        return ($len) ? $len : 0;
    }

    public function toArray($values)
    {
        $arrValues = [];
        if (empty($values)) {
            return $arrValues;
        }
        foreach ($values as $key => $value) {
            $arrValues[] = ArrayTool::toArray($value);
        }
        return $arrValues;
    }
}