<?php

namespace FSth\Framework\Server\Pack;

interface PackerInterface
{
    function setPackerHandler($handler);

    function encode($data);

    function decode($string);
}