<?php

class RequestKinTest extends \FSth\Framework\UnitTest\UnitTestCase
{
    private $requestKin;

    public function setUp()
    {
        parent::setUp();
        $traceConfig = [
            'setting' => [
                'logger_type' => 'http',
                'host' => 'http://127.0.0.1:9411',
            ]
        ];
        $this->requestKin = new \FSth\Framework\Extension\ZipKin\RequestKin("testServer", "127.0.0.1", "9503",
            $traceConfig['setting']);
        $this->requestKin->setRequestServer([
            'request_uri' => 'testUri1'
        ]);
    }

    public function testTrace()
    {
        $tracer = $this->requestKin->getTracer();
        sleep(1);
        $tracer->trace();
    }
}