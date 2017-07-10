<?php

namespace FSth\Framework\Tool;

use FSth\Framework\Extension\ZipKin\RequestKin;
use FSth\Framework\Context\Context;
use whitemerry\phpkin\Tracer;

class ZipKin
{
    /**
     * @param $serverConfig
     *  host
     *  port
     *  serverName
     * @param $traceConfig
     *  logger_type
     *  host
     *  file_path
     *  file_name
     * @param $traceHeader
     *  request_uri
     *  http_x_b3_traceid
     *  http_x_b3_spanid
     *  http_x_b3_sampled
     * @return Context|bool
     */
    public static function beforeExecute($serverConfig, $traceConfig, $traceHeader)
    {
        if (empty($traceConfig['execute']) || !$traceConfig['execute']) {
            return false;
        }
        $context = new Context();
        $requestKin = new RequestKin($serverConfig['serverName'], $serverConfig['host'], $serverConfig['port'],
            $traceConfig);
        $requestKin->setRequestServer($traceHeader);
        $tracer = $requestKin->getTracer();

        $context->traceId = $requestKin->getTraceId();
        $context->traceSpanId = $requestKin->getTraceSpanId();
        $context->sampled = $requestKin->getSampled();
        $context->tracer = $tracer;

        $GLOBALS['content'] = $context;
    }

    /**
     * @param $traceConfig
     *  logger_type
     *  host
     *  file_path
     *  file_name
     * @return bool
     */
    public static function afterExecute($traceConfig)
    {
        if (empty($traceConfig['execute'])) {
            return false;
        }
        if (empty($GLOBALS['context']) || !($GLOBALS['context'] instanceof Context)) {
            return false;
        }
        $tracer = $GLOBALS['context']->tracer;
        if ($tracer instanceof Tracer) {
            unset($GLOBALS['context']);
            $tracer->trace();
        }
        return true;
    }

    /**
     * @param $traceConfig
     *  logger_type
     *  host
     *  file_path
     *  file_name
     * @return array|bool
     */
    public function getTraceHeader($traceConfig)
    {
        if (empty($traceConfig['execute'])) {
            return false;
        }
        if (empty($GLOBALS['context']) || !($GLOBALS['context'] instanceof Context)) {
            return false;
        }
        return [
            'http_x_b3_traceid' => $GLOBALS['context']->traceId,
            'http_x_b3_spanid' => $GLOBALS['context']->traceSpanId,
            'http_x_b3_sampled' => $GLOBALS['context']->sampled,
        ];
    }
}