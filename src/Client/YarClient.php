<?php

namespace FSth\Framework\Client;

class YarClient extends BaseClient
{
    protected $client;
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
        $this->client = new \Yar_Client($url);
    }

    public function setOpt($name, $value)
    {
        $this->client->SetOpt($name, $value);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $this->arguments = !empty($arguments) ? $arguments : [];
        $this->name = $name;
        $this->beforeCall();
        $result = call_user_func_array([$this->client, $name], $this->arguments);
        $this->afterCall();
        return $result;
    }

    protected function initHostAndPort()
    {
        $parseUrl = parse_url($this->url);
        $this->host = $checkHost = $parseUrl['host'];
        $this->port = $port = !empty($parseUrl['port']) ? $parseUrl['port'] : 80;
    }
}