<?php

class RedisClientTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $host = '127.0.0.1';
    private $port = '9512';

    private $client;

    public function setUp()
    {
        $this->client = new \FSth\Framework\NoSql\Redis\Client($this->host, $this->port);
    }

    public function testSet()
    {
        $result = $this->client->set('a', 'eeee');
        var_dump($result);
    }
}