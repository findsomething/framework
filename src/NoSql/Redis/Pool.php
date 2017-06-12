<?php

namespace FSth\Framework\NoSql\Redis;

use FSth\Framework\Server\Pack\Packer as TcpPacker;
use FSth\Framework\Server\Pack\Handler as TcpHandler;
use FSth\Redis\Client;
use FSth\Redis\Proxy;

class Pool
{
    protected $host;
    protected $port;
    protected $pool;

    protected $logger;
    protected $tcpPacker;
    protected $count;

    protected $server;
    protected $options;
    protected $setting = [
        'max_connections' => 100,
        'worker_num' => 8,
        'open_tcp_nodelay' => 1,
        'task_worker_num' => 20,
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
    ];

    protected $redisCfg = [
        'host' => '127.0.0.1',
        'port' => '6379',
        'timeout' => 2,
        'num' => 100
    ];

    protected $binds = [
        'onReceive' => 'receive',
        'onTask' => 'task',
        'onFinish' => 'finish'
    ];

    public function __construct($host, $port, $options = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->tcpPacker = new TcpPacker();
        $this->tcpPacker->setPackerHandler(new TcpHandler());
        $this->options = $options;

        if (!empty($options['redis'])) {
            $this->redisCfg = array_merge($this->redisCfg, $options['redis']);
        }

        if (!empty($options['server'])) {
            $this->setting = array_merge($this->setting, $options['server']);
        }

        $this->pool = new \SplQueue();

        $this->createServer();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function daemonize()
    {
        $this->setting['daemonize'] = 1;
    }

    public function onReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $decode = $this->tcpPacker->decode($data);
        if (count($this->pool) == 0) {
            $client = new Client($this->redisCfg['host'], $this->redisCfg['port'], $this->redisCfg['timeout']);
            $proxy = new Proxy($client);
            $proxy->setLogger($this->logger);
            $proxy->connect();
            $this->pool->push($proxy);
            $this->count++;
        }
        var_dump($decode);
        var_dump(count($this->pool));
        if (!is_array($decode) || empty($decode['msg']) || $decode['msg'] != 'OK') {
            $server->send($fd, [
                'error' => '无法解析数据'
            ]);
        }
        $parseData = $decode['data'];
//        $result = $server->taskwait($decode);
        $redis = $this->pool->pop();
        try {
            $result = call_user_func_array([$redis, $parseData['method']], $parseData['args']);
            $server->send($fd, $this->tcpPacker->encode($result));
        } catch (\Exception $e) {
            $server->send($fd, $this->tcpPacker->encode($e->getMessage()));
        }
        $this->pool->push($redis);
    }

    public function onTask(\swoole_server $server, $taskId, $fromId, $data)
    {
//        $redis = $this->pool->pop();
//        $result = call_user_func_array([$redis, $data['method']], $data['args']);
//        $this->pool->push($redis);
//        $server->finish($result);
    }

    public function onFinish(\swoole_server $server, $data)
    {

    }

    public function listen()
    {
        register_shutdown_function(array($this, 'handleFatal'));
        $this->server->set($this->setting);

        $this->bind();
        $this->server->start();
    }

    public function handleFatal()
    {
        $error = error_get_last();
        $this->logger->error('fatalError', ['error' => $error]);
    }

    protected function createServer()
    {
        $this->server = new \swoole_server($this->host, $this->port);
    }

    protected function bind()
    {
        foreach ($this->binds as $method => $evt) {
            $this->server->on($evt, [$this, $method]);
        }
    }
}