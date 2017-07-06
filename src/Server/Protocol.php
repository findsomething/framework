<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 20:17
 */
namespace FSth\Framework\Server;

use FSth\Framework\Context\Context;
use FSth\Framework\Extension\ZipKin\RequestKin;
use FSth\Framework\Tool\StandardTool;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use FSth\Framework\Tool\ParseRaw;
use whitemerry\phpkin\Tracer;

class Protocol
{
    protected $setting;
    protected $logger;
    protected $kernel;
    protected $handle;
    protected $server;

    protected $context;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->logger = $this->kernel['logger'];
    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        if ($req->server['request_uri'] == '/favicon.ico') {
            $res->end();
            return;
        }
        $this->beforeRequest($req, $res);

        $this->makeup($req);

        try {
            ob_start();
            $response = $this->handle->handle();
            ob_end_clean();

            if ($response instanceof Response) {
                $res->status(200);
                $res->end($response->getContent());
            } else {
                throw new \Exception("unexpected response");
            }

        } catch (\Exception $e) {
            $error = StandardTool::toError($e->getMessage(), $e->getCode());
            $res->end(json_encode($error));
        } finally {
            $this->afterRequest($req, $res);
        }
    }

    public function onWorkerStart(\swoole_http_server $server, $workerId)
    {
        $this->server = $server;
        $kernel = clone $this->kernel;
        $this->handle = new Phalcon($kernel);
    }

    public function onReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $server->send($fd, $data);
    }

    protected function beforeRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        $this->context = new Context();
        $traceConfig = $this->kernel->config('trace');
        if (empty($traceConfig['execute']) || !$traceConfig['execute']) {
            return false;
        }
        $serverConfig = $this->kernel->config('server');
        $serverName = !empty($serverConfig['name']) ? $serverConfig['name'] : "test";

        $this->initTrace($serverName, $serverConfig['host'], $serverConfig['port'], $traceConfig['setting'],
            $req->server);
    }

    protected function afterRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        $this->handleAfter();
    }

    protected function beforeReceive(\swoole_server $server, $fd, $fromId, $data)
    {

    }

    protected function afterReceive(\swoole_server $server, $fd, $fromId, $data)
    {

    }

    protected function handleAfter()
    {
        $traceConfig = $this->kernel->config('trace');
        if (empty($traceConfig['execute'])) {
            return false;
        }
        if (empty($GLOBALS['context']) || !($GLOBALS['context'] instanceof Context)) {
            return false;
        }
        $tracer = $GLOBALS['context']->tracer;
        if ($tracer instanceof Tracer) {
            try {
                $tracer->trace();
            } catch (\Exception $e) {
                $this->logger->error('trace error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
        }
        unset($GLOBALS['context']);
    }

    protected function initTrace($serverName, $host, $port, $setting, $traceHeader)
    {
        $requestKin = new RequestKin($serverName, $host, $port, $setting);

        $requestKin->setRequestServer($traceHeader);
        $tracer = $requestKin->getTracer();

        $this->context->traceId = $requestKin->getTraceId();
        $this->context->traceSpanId = $requestKin->getTraceSpanId();
        $this->context->sampled = $requestKin->getSampled();
        $this->context->tracer = $tracer;

        $GLOBALS['context'] = $this->context;
    }

    protected function setOptions($options)
    {
        $this->setting = $options;
    }

    protected function setLogger($logger)
    {
        $this->logger = $logger;
    }

    protected function makeup(\swoole_http_request $req)
    {

        $_GET = $_POST = $_COOKIE = $_SERVER = array();
        $this->handle->setHeader(array());
        $this->handle->setRaw("");
        $_SERVER = $req->server;

        if ($req->server['request_method'] == 'POST') {
            $parseRaw = new ParseRaw($req->header['content-type'], $req->rawContent());
            $_POST = $parseRaw->parse();
        }

        $this->handle->setRaw($req->rawContent());

        if (!empty($req->get)) {
            $_GET = $req->get;
        }
        if (!empty($req->header)) {
            $this->handle->setHeader($req->header);
        }
        $_GET['_url'] = $_SERVER['REQUEST_URI'] = $req->server['request_uri'];
        $_SERVER['REQUEST_METHOD'] = $req->server['request_method'];

        return true;
    }
}