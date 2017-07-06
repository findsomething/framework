<?php

namespace FSth\Framework\Client;

use FSth\Framework\Extension\ZipKin\ClientKin;

class BaseClient
{
    protected $arguments;
    protected $name;
    protected $serverName;
    protected $service;

    protected $host;
    protected $port;

    protected $clientKin;

    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
    }

    protected function beforeCall()
    {
        $context = !empty($GLOBALS['context']) ? $GLOBALS['context'] : null;
        $this->clientKin = new ClientKin($context);
        if ($this->clientKin->needTrace()) {
            array_push($this->arguments, ['traceHeader' => $this->clientKin->traceHeader()]);
        }

    }

    protected function afterCall()
    {
        if ($this->clientKin->needTrace()) {
            $this->clientKin->addSpan($this->serverName, $this->host, $this->port, $this->service . "_" . $this->name);
            $GLOBALS['context']->tracer = $this->clientKin->getTracer();
        }
    }
}