<?php

namespace FSth\Framework\Extension\ZipKin;

use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Endpoint;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Span;
use whitemerry\phpkin\Tracer;

class ZipKin
{
    protected $serverName;
    protected $host;
    protected $port;

    protected $traceLogger;

    protected $traceId;
    protected $traceSpanId;
    protected $isSampled;

    protected $endPoint;

    public function __construct($serverName, $host, $port)
    {
        $this->serverName = $serverName;
        $this->host = $host;
        $this->port = $port;
        $this->endPoint = $this->createEndpoint();
    }

    public function setTraceLogger($traceLogger)
    {
        $this->traceLogger = $traceLogger;
    }

    public function getEndpoint()
    {
        return $this->endPoint;
    }

    public function setBackEndParams($traceId = null, $traceSpanId = null, $isSampleId = true)
    {
        $this->traceId = $traceId;
        $this->traceSpanId = $traceSpanId;
        $this->isSampled = $isSampleId;
    }

    public function createTracer($traceName, $frontend = true)
    {
        return ($frontend) ? $this->createFrontendTracer($traceName) : $this->createBackendTracer($traceName);
    }

    public function createSpan($spanName, $spanId = null, $requestStart = null)
    {
        $spanId = !empty($spanId) ? $spanId : new SpanIdentifier();
        $requestStart = !empty($requestStart) ? $requestStart : zipkin_timestamp();
        $annotationBlock = new AnnotationBlock($this->endPoint, $requestStart);
        return new Span($spanId, $spanName, $annotationBlock);
    }

    protected function createEndpoint()
    {
        return new Endpoint($this->serverName, $this->host, $this->port);
    }

    protected function createFrontendTracer($traceName)
    {
        $tracer = new Tracer(
            $traceName,
            $this->endPoint,
            $this->traceLogger
        );
        $tracer->setProfile(Tracer::FRONTEND);
        return $tracer;
    }

    protected function createBackendTracer($traceName)
    {
        $tracer = new Tracer(
            $traceName,
            $this->endPoint,
            $this->traceLogger,
            $this->isSampled,
            $this->traceId,
            $this->traceSpanId
        );
        $tracer->setProfile(Tracer::BACKEND);
        return $tracer;
    }

}