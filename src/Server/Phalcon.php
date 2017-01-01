<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 21:18
 */
namespace FSth\Framework\Server;

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
    protected $header = array();

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->route = array();
        if($kernel->config('route_file') && file_exists($kernel->config('route_file'))){
            $this->route = include $kernel->config('route_file');
        }
        $this->namespace = $kernel['namespace'];
        $this->app = new Micro();
        $this->app['kernel'] = $kernel;
        $this->mount = new Mount($this->app);

        $this->logger = $kernel['logger'];
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

        $this->app->get('/', function () {
            $response = new Response();
            $response->setStatusCode(200);

            return $response->setJsonContent(array(
                'hello' => 'world'
            ));
        });

        $this->app->before(function () {
        });


        $this->app->error(function (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(200);
            $traceId = time() . '_' . substr(hash('md5', uniqid('', true)), 0, 10);

            return $response->setJsonContent(array(
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace_id' => $traceId
                )
            ));
        });

        $this->app->notFound(function () {
            $response = new Response();
            $response->setStatusCode(200);

            return $response->setJsonContent(array(
                'error' => array(
                    'message' => 'not found',
                )
            ));
        });
    }

}