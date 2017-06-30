<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 21:18
 */
namespace FSth\Framework\Server;

use FSth\Framework\Tool\StandardTool;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use FSth\Framework\Tool\Mount;

class Phalcon
{
    protected $kernel;
    protected $app;
    protected $route;
    protected $mount;
    protected $namespace;

    protected $logger;

    protected $workerId = 0;
    protected $di;
    protected $server;
    protected $header = [];
    protected $raw = "";

    protected $binds = [
        'before' => 'onBefore',
        'notFound' => 'onNotFound'
    ];

    public function __construct($kernel, $addErrorTrigger = false)
    {
        $this->kernel = $kernel;
        $this->route = [];
        if ($kernel->config('route_file') && file_exists($kernel->config('route_file'))) {
            $this->route = include $kernel->config('route_file');
        }
        $this->namespace = $kernel['namespace'];
        $this->app = new Micro();
        $this->app['kernel'] = $kernel;
        $this->mount = new Mount($this->app);

        $this->logger = $kernel['logger'];

        if ($addErrorTrigger) {
            $this->binds['error'] = 'onError';
        }

        $this->init();
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    public function handle()
    {
        return $this->app->handle();
    }

    protected function init()
    {
        $this->di = new FactoryDefault();
        $this->app->setDi($this->di);
        $this->mount->setPath($this->namespace . 'Controller');
        $this->mount->mount($this->route);

        $this->app->get('/', $this->helloWorld());

        foreach ($this->binds as $key => $method) {
            $this->app->$key($this->$method());
        }
    }

    protected function helloWorld()
    {
        return function () {
            $response = new Response();
            $response->setStatusCode(200);

            return $response->setJsonContent(array(
                'hello' => 'world'
            ));
        };
    }

    protected function onBefore()
    {
        return function () {
        };
    }

    protected function onError()
    {
        return function (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(200);

            return $response->setJsonContent(StandardTool::toError($e->getMessage(), $e->getCode()));
        };
    }

    protected function onNotFound()
    {
        return function () {
            $response = new Response();
            $response->setStatusCode(500);

            return $response->setJsonContent(StandardTool::toError("Route not found", 0));
        };
    }
}