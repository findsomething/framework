<?php

namespace FSth\Framework\Server\Pack;

interface PackerInterface
{
    function setPackerHandler($handler);

    function encode(array $data);

    function decode($string);
}