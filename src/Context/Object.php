<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/8
 * Time: 14:53
 */
namespace FSth\Framework\Context;

class Object
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }
}