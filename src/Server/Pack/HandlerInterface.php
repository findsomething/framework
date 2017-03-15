<?php

namespace FSth\Framework\Server\Pack;

interface HandlerInterface
{
    public function encode(array $data);

    public function decode($string);
}