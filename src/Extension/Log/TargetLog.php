<?php

namespace FSth\Framework\Extension\Log;

use FSth\Framework\Exception\FsException;
use FSth\Framework\Tool\ArrayTool;

abstract class TargetLog
{
    protected $storage;

    protected $targetType;
    protected $targetId;

    protected $allowLevel = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    public function __construct($targetType, $targetId)
    {
        $this->targetType = $targetType;
        $this->targetId = $targetId;
    }

    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
    }

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    abstract protected function save($array);

    /**
     * @param $name
     *  only allow allowLevel
     * @param $arguments
     *  message
     *  content array
     *      clientId option
     *      action option
     *      ip option
     *      other anything else
     * @throws FsException
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $this->check($name, $arguments);
        $this->save($this->toArray($name, $arguments['message'], $arguments['content']));
    }

    protected function toArray($level, $message, $content)
    {
        $clientId = !empty($content['clientId']) ? $content['clientId'] : '';
        $action = !empty($content['action']) ? $content['action'] : '';
        $ip = !empty($content['ip']) ? $content['ip'] : '';

        unset($content['clientId']);
        unset($content['action']);
        unset($content['ip']);

        return [
            'targetType' => $this->targetType,
            'targetId' => $this->targetId,
            'action' => $action,
            'level' => strtolower($level),
            'message' => $message,
            'content' => ArrayTool::toString($content),
            'clientId' => $clientId,
            'ip' => $ip
        ];
    }

    protected function check($name, $argument)
    {

        if (!in_array(strtolower($name), $this->allowLevel)) {
            throw new FsException("未允许日志类型");
        }
        if (!is_array($argument) || ArrayTool::requireds($argument, ['message', 'content']) ||
            !is_array($argument['content'])
        ) {
            throw new FsException("未允许日志参数");
        }
    }
}