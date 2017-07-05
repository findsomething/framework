<?php

namespace FSth\Framework\Extension\ZipKin;

use whitemerry\phpkin\Identifier\SpanIdentifier;

class ClientKin
{
    protected $context;
    protected $parser;
    protected $flag;

    protected $spanId;
    protected $requestStart;

    protected $ip;
    protected $port;

    public function __construct($context)
    {
        $this->context = $context;
        $this->parser = new ParserKin($this->context);

        $this->flag = $this->parser->parse();

        $this->spanId = new SpanIdentifier();
        $this->requestStart = zipkin_timestamp();
    }

    public function needTrace()
    {
        return $this->flag;
    }

    public function traceHeader()
    {
        if (!$this->needTrace()) {
            return [];
        }
        return [
            'x-b3-traceid' => $this->parser->tracerId,
            'x-b3-spanid' => ((string)$this->spanId),
            'x-b3-parentspanid' => $this->parser->traceSpanId,
            'x-b3-sampled' => ((int)$this->parser->sampled)
        ];
    }

    public function addSpan($serverName, $host, $port, $functionName)
    {
        $this->ip = $host;
        $this->port = $port;
        $this->changeToIp();
        while (1) {
            if (filter_var($this->ip, FILTER_VALIDATE_IP) || empty($this->ip)) {
                break;
            }
        }
        $zipKin = new ZipKin($serverName, $this->ip, $this->port);
        $this->parser->tracer = $zipKin->createSpan($functionName, $this->spanId, $this->requestStart);
    }

    public function getTracer()
    {
        return $this->parser->tracer;
    }

    protected function changeToIp()
    {
        $checkHost = $this->ip;
        if (filter_var($checkHost, FILTER_VALIDATE_IP) === false) {
            swoole_async_dns_lookup($checkHost, function ($host, $ip) {
                $this->ip = $ip;
            });
        }
    }
}