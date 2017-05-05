<?php

class SYarClientTest extends PHPUnit_Framework_TestCase
{
    private $host = '127.0.0.1';
    private $port = '9511';

    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \FSth\Framework\Client\SYarClient($this->host, $this->port, "HealthService");
    }

    public function testClient()
    {
        $result = $this->client->health();
        var_dump($result);
    }
}
