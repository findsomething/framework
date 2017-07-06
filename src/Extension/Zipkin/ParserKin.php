<?php

namespace FSth\Framework\Extension\ZipKin;

use FSth\Framework\Context\Context;
use whitemerry\phpkin\Tracer;

class ParserKin
{
    public $tracer;
    public $tracerId;
    public $traceSpanId;
    public $sampled;

    protected $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function parse()
    {
        if ($this->valid() && $this->context->tracer instanceof Tracer) {
            $this->tracer = $this->context->tracer;
            $this->tracerId = $this->context->tracerId;
            $this->traceSpanId = $this->context->traceSpanId;
            $this->sampled = $this->context->sampled;
            return true;
        }
        return false;
    }

    public function valid()
    {
        if (!($this->context instanceof Context)) {
            return false;
        }
        return true;
    }
}