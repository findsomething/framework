<?php

class YarClientTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $url = "http://127.0.0.1:9503/";

    public function setUp()
    {
        parent::setUp();
    }

    public function testSYar()
    {
        $client = new \FSth\Framework\Client\YarClient($this->url . "?service=TestService");
        $client->setServerName("testClient");
        $params = array('hello' => 'world');

        $result = $client->giveBack($params);
        var_dump($result);
//
//        $client = new \FSth\Framework\Client\YarClient("http://127.0.0.1:9509?service=HealthService");
//        $client->setServerName("liveRpcService");
//        $result = $client->health();
//        var_dump($result);
    }
}