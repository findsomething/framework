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

    const CONNECT_ERROR = 60001;
    const RECEIVE_ERROR = 60002;

    protected $client;

    protected $code;
    protected $error;

    protected $service;

    protected $options;

    protected $packer;
    protected $parser;
    protected $timeout;
    protected $keepLive;

    protected $setting = [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 1024 * 1024 * 2,
        'open_tcp_nodelay' => 1,
        'socket_buffer_size' => 1024 * 1024 * 4,
    ];

    public function __construct($host, $port, $service, $options = [], $keepLive = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->service = $service;
        $this->options = $options;
        $this->keepLive = $keepLive;

        $this->tcpConnect();
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        try {
            $this->arguments = !empty($arguments) ? $arguments : [];
            $this->name = $name;

            $this->beforeCall();
            if (empty($this->client) || !($this->client instanceof \swoole_client) ||
                $this->client->isConnected() === false
            ) {
                $this->reconnectTcpConnet();
            }
            $ret = $this->client->send($this->packer->encode(Format::client($this->service, $name, $arguments)));
            $this->checkTcpSendResult($ret);

            $receive = $this->waitTcpResult();
            $result = $this->packer->decode($receive);
            return $this->parser->parse($result['data']);
        } catch (\Exception $e) {
            if (($e->getCode() == self::CONNECT_ERROR || $e->getCode() == self::RECEIVE_ERROR) ||
                strpos($e->getMessage(), 'Broken pipe') !== false
            ) {
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

    public function tcpClose()
    {
        try {
            if (!empty($this->client) && $this->client instanceof \swoole_client) {
                $this->client->close(true);
            }
        } catch (\Exception $e) {

        } finally {
            unset($this->client);
            $this->client = null;
        }
    }

    private function tcpConnect()
    {
        $socketType = $this->keepLive ? (SWOOLE_TCP | SWOOLE_KEEP) : SWOOLE_TCP;
        $this->client = new \swoole_client($socketType);
        $this->setting = $this->setting + $this->options;
        $this->client->set($this->setting);

        $this->packer = new Packer();
        $this->packer->setPackerHandler(new Handler());
        $this->parser = new Parser();

        $this->timeout = self::RECEIVE_TIMEOUT;

        $connected = $this->client->connect($this->host, $this->port, $this->timeout);
        $this->checkTcpSendResult($connected);
    }

    private function waitTcpResult()
    {
        if (false === ($result = $this->client->recv())) {
            throw new FsException("receive time out. code:" . $this->client->errCode . " msg:" .
                \socket_strerror($this->client->errCode), self::RECEIVE_ERROR);
        }
        return $result;
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

    private function reconnectTcpConnet()
    {
        $this->tcpClose();
        $this->tcpConnect();
    }
}