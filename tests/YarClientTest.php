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
        $client->SetOpt(YAR_OPT_TIMEOUT, empty($config['timeout']) ? 5000 : $config['timeout']);
        $client->SetOpt(YAR_OPT_PACKAGER, empty($config['packager']) ? 'php' : $config['packager']);
        $client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, empty($config['connectTimeout']) ? 2000 : $config['connectTimeout']);
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