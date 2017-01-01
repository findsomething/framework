<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/1
 * Time: 15:39
 */
namespace FSth\Framework\Demo\Controller;

use FSth\Framework\Controller\BaseController;

class TestController extends BaseController
{
    public function test()
    {
        return $this->toResponse(array(
            'test' => 'world'
        ));
    }
}