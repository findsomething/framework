<?php

class SYarClientTest extends PHPUnit_Framework_TestCase
{
    private $host = '127.0.0.1';
    private $port = '9508';

    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \FSth\Framework\Client\SYarClient($this->host, $this->port, "HealthService", [], false);
        $this->client->setServerName("LiveTcpService");
    }

    public function testClient()
    {
        $result = $this->client->health();
        var_dump($result);
        
        $this->client->tcpClose();

//        $result = $this->client->giveBack(['hello' => 'world']);
//        var_dump($result);
    }
}
