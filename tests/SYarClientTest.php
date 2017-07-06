<?php

class SYarClientTest extends PHPUnit_Framework_TestCase
{
    private $host = '127.0.0.1';
    private $port = '9504';

    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \FSth\Framework\Client\SYarClient($this->host, $this->port, "TestService");
        $this->client->setServerName("syarClient");
    }

    public function testClient()
    {
//        $client = new \FSth\Framework\Client\SYarClient($this->host, "9511", "HealthService");
//        $client->setServerName("LiveTcpService");
//        $result = $client->health();
//        var_dump($result);

        $result = $this->client->giveBack(['hello' => 'world']);
        var_dump($result);
    }
}
