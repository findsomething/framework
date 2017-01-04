<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/30
 * Time: 16:13
 */
namespace FSth\Framework\Tool;

class ParseRaw
{
    const URL_ENCODE = "application/x-www-form-urlencoded";

    const JSON = "application/json";

    const FORM_DATA = "multipart/form-data";

    private $supportMethods;

    private $method;
    private $func;
    private $rawContent;

    public function __construct($method, $rawContent)
    {
        $this->supportMethods = array(
            self::URL_ENCODE => 'urlEncode',
            self::JSON => 'json',
            self::FORM_DATA => 'formData'
        );
        $this->method = $method;

        $this->rawContent = $rawContent;
    }

    public function parse()
    {
        if (!$this->setFunc()) {
            return array();
        }
        return call_user_func_array(array($this, $this->supportMethods[$this->func]), array());
    }

    protected function setFunc()
    {
        $keys = array_keys($this->supportMethods);
        $this->func = strtolower($this->method);
        if (in_array($this->func, array_keys($this->supportMethods))) {
            return true;
        }
        foreach ($keys as $key) {
            if (strpos($this->func, $key) !== false) {
                $this->func = $key;
                return true;
            }
        }
        return false;
    }

    protected function urlEncode()
    {
        $result = array();
        parse_str($this->rawContent . "", $result);
        return $result;
    }

    protected function json()
    {
        return json_decode($this->rawContent, true);
    }

    protected function formData()
    {
        $result = array();

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $this->method, $matches);
        // content type is probably regular form-encoded
        if (!count($matches)) {
            return $result;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $blocks = preg_split("/-+{$boundary}/", $this->rawContent);
        array_pop($blocks);

        // loop data blocks
        foreach ($blocks as $id => $block) {
            if (empty($block)) {
                continue;
            }

            preg_match('/name=".*"[\r|\n]*.*/s', $block, $matches);
            $parse = preg_split('/"/s', $matches[0]);
            if (count($parse) != 3) {
                continue;
            }
            $result[trim($parse[1])] = trim($parse[2]);
        }
        return $result;
    }
}