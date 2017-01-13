<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 18:41
 */
namespace FSth\Framework\Server;

class HttpServer
{
    protected $host = "0.0.0.0";
    protected $port;

    protected $server;
    protected $setting = array(
        'max_connection' => 100,       //worker process num
        'worker_num' => 8,       //worker process num
        'max_request' => 10000,
        'backlog' => 128,        //listen backlog
        'open_tcp_keepalive' => 1,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
        'http_parse_post' => false,
    );
    protected $initBinds = array(
        'onServerStart' => 'ManagerStart',
        'onServerStop' => 'ManagerStop',
    );

    protected $binds = array(
        'onWorkerStart' => 'WorkerStart',
        'onRequest' => 'request'
    );

    protected $config;

    protected $protocol;
    protected $logger;
    protected $kernel;

    public function __construct($host, $port, $options = array())
    {
        $this->setting = $this->setting + $options;
        $this->host = $host;
        $this->port = $port;
        $this->server = $this->createServer();
    }

    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
        $this->config = $kernel->config('server');
        $this->setting = $this->setting + $this->config['setting'];
    }

    public function setOptions($options)
    {
        $this->setting = $options + $this->setting;
    }

    public function daemonize()
    {
        $this->setting['daemonize'] = 1;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function listen()
    {
        register_shutdown_function(array($this, 'handleFatal'));

        $this->server->set($this->setting);
        $this->init();
        $this->bind();

        $this->server->start();
    }

    public function onServerStart($serv)
    {
        $this->logger->info("Server start on {$this->host}:{$this->port}, pid {$serv->master_pid}");
        if (!empty($this->config['pid_file'])) {
            file_put_contents($this->config['pid_file'], $serv->master_pid);
        }
    }

    public function onServerStop($serv)
    {
        $this->logger->info("Server stop pid {$serv->master_pid}");
        if (!empty($this->config['pid_file']) && file_exists($this->config['pid_file'])) {
            unlink($this->config['pid_file']);
        }
    }

    public function handleFatal()
    {
        $error = error_get_last();
        $this->logger->error('fatalError', $error);
    }

    protected function init()
    {

    }

    protected function createServer()
    {
        return new \swoole_http_server($this->host, $this->port);
    }

    protected function bind()
    {
        foreach ($this->initBinds as $method => $evt) {
            $this->server->on($evt, array($this, $method));
        }

        foreach ($this->binds as $method => $evt) {
            if (method_exists($this->protocol, $method)) {
                $this->server->on($evt, array($this->protocol, $method));
            }
        }
    }
}