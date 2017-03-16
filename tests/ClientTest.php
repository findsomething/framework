<?php

class ClientTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $host = '127.0.0.1';
    private $port = '9504';

    private $client;

    private $packer;

    public function setUp()
    {
        $this->client = new \FSth\Framework\Client\Client($this->host, $this->port);
        $this->client->connect();

        $this->packer = new \FSth\Framework\Server\Pack\Packer();
        $this->packer->setPackerHandler(new \FSth\Framework\Server\Pack\Handler());
    }

    public function testSend()
    {
        $this->client->send($this->packer->encode([
            'hello' => 'world'
        ]));
        $result = $this->client->receive();
        $decode = $this->packer->decode($result);
        $this->assertEquals('world', $decode['data']['hello']);
    }
}
