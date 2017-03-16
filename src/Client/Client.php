<?php

namespace FSth\Framework\Client;

class Client
{
    const RECEIVE_TIMEOUT = 3;

    protected $host;
    protected $port;

    protected $client;

    protected $code;
    protected $error;

    protected $setting = [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 1024 * 1024 * 2,
        'open_tcp_nodelay' => 1,
        'socket_buffer_size' => 1024 * 1024 * 4,
    ];

    public function __construct($host, $port, $options = [])
    {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $this->host = $host;
        $this->port = $port;

        $this->setting = $this->setting + $options;
    }

    public function connect()
    {
        $this->client->set($this->setting);

        $connected = $this->client->connect($this->host, $this->port, self::RECEIVE_TIMEOUT);
        if (!$connected) {
            $this->triggerError();
        }
    }

    public function send($message)
    {
        $this->client->send($message);
    }

    public function receive()
    {
        return $this->client->recv();
    }

    private function triggerError()
    {
        $this->code = $this->client->errCode;
        if ($this->code == 0) {
            $this->error = "Connect fail.Please check the host dns.";
            $this->code = -1;
        } else {
            $this->error = \socket_strerror($this->code);
        }
        throw new \Exception($this->error, $this->code);
    }
}