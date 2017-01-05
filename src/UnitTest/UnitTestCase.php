<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/8
 * Time: 17:23
 */
namespace FSth\Framework\UnitTest;

class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected $load = false;
    protected $kernel;

    public function setUp()
    {
        parent::setUp();
        $this->load = true;
    }

    public function getKernel()
    {
        return $this->kernel;
    }
}