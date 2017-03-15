<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 20:17
 */
namespace FSth\Framework\Server;

use Phalcon\Mvc\Micro;
use FSth\Framework\Tool\ParseRaw;

class Protocol
{
    protected $setting;
    protected $logger;
    protected $kernel;
    protected $handle;
    protected $server;

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

        $this->makeup($req);

        try {
            ob_start();
            $this->handle->handle();
            $result = ob_get_contents();
            ob_end_clean();

            $res->status(200);
            $res->end($result);
        } catch (\Exception $e) {
            $res->end($e->getMessage());
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