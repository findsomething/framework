<?php

namespace FSth\Framework\Extension\ZipKin;

use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
use whitemerry\phpkin\Logger\FileLogger;
use whitemerry\phpkin\Logger\SimpleHttpLogger;
use whitemerry\phpkin\TracerInfo;

class RequestKin
{
    protected $config;
    protected $execute;

    protected $traceLogger;

    protected $server;

    protected $traceId;
    protected $traceSpanId;
    protected $isSampled;

    protected $uri;

    /**
     * ServerKin constructor.
     * @param $serverName
     * @param $host
     * @param $port
     * @param $config
     *  logger_type http/file
     *  host
     *  file_path
     *  file_name
     */
    public function __construct($serverName, $host, $port, $config)
    {
        $this->config = $config;

        $this->initTraceLogger();

        $this->server = new ZipKin($serverName, $host, $port);
        $this->server->setTraceLogger($this->traceLogger);
    }

    public function setRequestServer($requestServer)
    {

        $this->traceId = !empty($requestServer['http_x_b3_traceid']) ?
            new TraceIdentifier($requestServer['http_x_b3_traceid']) : null;
        $this->traceSpanId = !empty($requestServer['http_x_b3_spanid']) ?
            new SpanIdentifier($requestServer['http_x_b3_spanid']) : null;
        $this->isSampled = !empty($requestServer['http_x_b3_sampled']) ? false : true;

        $this->server->setBackEndParams($this->traceId, $this->traceSpanId, $this->isSampled);

        $this->uri = $requestServer['request_uri'];
    }

    public function getTracer()
    {
        $tracer = $this->server->createTracer($this->uri, false);
        $this->resetWithTrace();
        return $tracer;
    }

    public function getTraceId()
    {
        return $this->traceId;
    }

    public function getTraceSpanId()
    {
        return $this->traceSpanId;
    }

    public function getSampled()
    {
        return $this->isSampled;
    }

    protected function initTraceLogger()
    {
        if ($this->config['logger_type'] == 'http') {
            $this->traceLogger = new SimpleHttpLogger([
                'host' => $this->config['host'],
                'muteErrors' => false,
            ]);
        } else if ($this->config['logger_type'] == 'file') {
            $this->traceLogger = new FileLogger([
                'path' => $this->config['file_path'],
                'fileName' => !empty($this->config['file_name']) ? $this->config['file_name'] : 'zipkin.log'
            ]);
        }
    }

    protected function resetWithTrace()
    {
        $this->traceId = (string)TracerInfo::getTraceId();
        $this->traceSpanId = (string)TracerInfo::getTraceSpanId();
        $this->isSampled = (bool)TracerInfo::isSampled();
    }

}