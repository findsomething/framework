<?php

namespace FSth\Framework\Server;

class MultiServer extends HttpServer
{
    protected $tcpServer = null;

    protected $setting = [
        'max_connection' => 100,       //worker process num
        'worker_num' => 8,       //worker process num
        'max_request' => 0,
        'backlog' => 3000,        //listen backlog
        'open_tcp_keepalive' => 1,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
        'http_parse_post' => false,

        'package_max_length' => 2097152, // 1024 * 1024 * 2,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, //1024 * 1024 * 32,
        'open_tcp_nodelay' => 1,
        'open_cpu_affinity' => 1,
    ];

    protected $tcpSetting = [

        'package_max_length' => 2097152, // 1024 * 1024 * 2,
        'buffer_output_size' => 3145728, //1024 * 1024 * 3,
        'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,

        'open_tcp_nodelay' => 1,
        'backlog' => 3000,
    ];

    public function __construct($host, $port, $options = [])
    {
        $this->setting = $this->setting + $options;

        $this->host = $host;
        $this->port = $port;
        $this->server = $this->createServer();
    }

    public function setTcp($tcpPort, $tcpConfig)
    {
        $this->tcpSetting = $this->tcpSetting + $tcpConfig;
        $this->tcpServer = $this->server->addlistener($this->host, $tcpPort, SWOOLE_TCP);
    }

    protected function bind()
    {
        parent::bind();
        if (method_exists($this->protocol, 'onReceive') && $this->tcpServer) {
            $this->tcpServer->on('Receive', [$this->protocol, 'onReceive']);
        }
    }

    public function listen()
    {
        register_shutdown_function(array($this, 'handleFatal'));

        $this->server->set($this->setting);
        if (!empty($this->tcpServer)) {
            $this->tcpServer->set($this->tcpSetting);
        }


        $this->init();
        $this->bind();

        $this->server->start();
    }
}