<?php

namespace FSth\Framework\Client;

use FSth\Framework\Server\Pack\Handler;
use FSth\Framework\Server\Pack\Packer;
use FSth\Framework\Tool\Parser;
use FSth\Framework\Tool\Format;
use FSth\Framework\Exception\FsException;

class SYarClient extends BaseClient
{
    const RECEIVE_TIMEOUT = 10;

    const CONNECT_ERROR = 2;
    const RECEIVE_ERROR = 3;

    protected $client;

    protected $code;
    protected $error;

    protected $service;

    protected $packer;
    protected $parser;
    protected $timeout;

    protected $setting = [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 1024 * 1024 * 2,
        'open_tcp_nodelay' => 1,
        'socket_buffer_size' => 1024 * 1024 * 4,
    ];

    public function __construct($host, $port, $service, $options = [])
    {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $this->setting = $this->setting + $options;
        $this->service = $service;
        $this->client->set($this->setting);

        $this->packer = new Packer();
        $this->packer->setPackerHandler(new Handler());
        $this->parser = new Parser();

        $this->host = $host;
        $this->port = $port;

        $this->timeout = self::RECEIVE_TIMEOUT;

        $this->tcpConnect();
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        try {
            $this->arguments = !empty($arguments) ? $arguments : [];
            $this->name = $name;

            $this->beforeCall();
            if (empty($this->client) || $this->client->isConnected() === false) {
                $this->tcpConnect();
            }
            $ret = $this->client->send($this->packer->encode(Format::client($this->service, $name, $arguments)));
            $this->checkTcpSendResult($ret);

            $receive = $this->waitTcpResult();
            $result = $this->packer->decode($receive);
            return $this->parser->parse($result['data']);
        } catch (\Exception $e) {
            if (($e->getCode() == 2 || $e->getCode() == 3) && strpos($e->getMessage(), 'Broken pipe') !== false) {
                $this->tcpClose();
            }
            throw new FsException($e->getMessage(), $e->getCode());
        } finally {
            $this->afterCall();
        }
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function waitTcpResult()
    {
        while (1) {
            $result = $this->client->recv();
            if ($result !== false && $result != "") {
                return $result;
            }
            throw new FsException("receive time out", self::RECEIVE_ERROR);
        }
    }

    private function checkTcpSendResult($ret)
    {
        if (!empty($ret)) {
            return;
        }
        $errorCode = $this->client->errCode;

        $msg = ($errorCode == 0) ? "Connect fail. Check host dns." : \socket_strerror($errorCode);

        throw new FsException($msg, self::CONNECT_ERROR);
    }

    private function tcpClose()
    {
        try {
            $this->client->close(true);
        } catch (\Exception $e) {

        } finally {
            $this->client = null;
        }
    }

    private function tcpConnect()
    {
        $connected = $this->client->connect($this->host, $this->port, $this->timeout);
        $this->checkTcpSendResult($connected);
    }
}