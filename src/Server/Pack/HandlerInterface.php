<?php

namespace FSth\Framework\Server\Pack;

interface HandlerInterface
{
    public function encode($data);

    public function decode($string);
}