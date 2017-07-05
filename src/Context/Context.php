<?php

namespace FSth\Framework\Context;

class Context
{
    public function __get($name)
    {
        if (!empty($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}