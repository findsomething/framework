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
        $this->initHostAndPort();
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
        try {
            $this->beforeCall();
            $result = call_user_func_array([$this->client, $name], $this->arguments);
            return $result;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->afterCall();
        }
    }

    protected function initHostAndPort()
    {
        $parseUrl = parse_url($this->url);
        $query = [];
        parse_str($parseUrl['query'], $query);
        $this->host = $checkHost = $parseUrl['host'];
        $this->port = $port = !empty($parseUrl['port']) ? $parseUrl['port'] : 80;
        $this->service = !empty($query['service']) ? $query['service'] : '';
    }
}