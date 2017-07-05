<?php

class ZipKinTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $zipKinServer1;
    private $zipKinServer2;

    private $traceLogger;

    public function setUp()
    {
        $this->traceLogger = new \whitemerry\phpkin\Logger\SimpleHttpLogger([
            'host' => 'http://127.0.0.1:9411', 'muteErrors' => false
        ]);
        $this->zipKinServer1 = new \FSth\Framework\Extension\ZipKin\ZipKin('server1', '127.0.0.1', '9501');
        $this->zipKinServer1->setTraceLogger($this->traceLogger);

        $this->zipKinServer2 = new \FSth\Framework\Extension\ZipKin\ZipKin('server2', '127.0.0.1', '9502');
        $this->zipKinServer2->setTraceLogger($this->traceLogger);
    }

    public function testTrace()
    {
        $tracer = $this->zipKinServer1->createTracer('testUri', false);
        var_dump($tracer);

        $span = $this->zipKinServer2->createSpan("testGet");

        $tracer->addSpan($span);


        $result = $tracer->trace();
        var_dump($result);
    }
}