<?php

namespace FSth\Framework\Server\Pack;

class Packer implements PackerInterface
{
    private $handler;
    
    public function setPackerHandler($handler)
    {
        // TODO: Implement setPackerHandler() method.
        $this->handler = $handler;
    }

    public function encode($data)
    {
        // TODO: Implement encode() method.
        return $this->handler->encode($data);
    }

    public function decode($string)
    {
        // TODO: Implement decode() method.
        return $this->handler->decode($string);
    }
}