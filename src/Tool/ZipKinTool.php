<?php

namespace FSth\Framework\Tool;

use FSth\Framework\Extension\ZipKin\RequestKin;
use FSth\Framework\Context\Context;
use whitemerry\phpkin\Tracer;

class ZipKinTool
{
    /**
     * @param $serverConfig
     *  host
     *  port
     *  name
     * @param $traceConfig
     *  execute
     *  setting
     *   logger_type
     *   host
     *   file_path
     *   file_name
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
        $serverName = !empty($serverConfig['name']) ? $serverConfig['name'] : "test";
        $context = new Context();
        $requestKin = new RequestKin($serverName, $serverConfig['host'], $serverConfig['port'],
            $traceConfig['setting']);
        $requestKin->setRequestServer($traceHeader);
        $tracer = $requestKin->getTracer();

        $context->traceId = $requestKin->getTraceId();
        $context->traceSpanId = $requestKin->getTraceSpanId();
        $context->sampled = $requestKin->getSampled();
        $context->tracer = $tracer;

        $GLOBALS['context'] = $context;

    }

    /**
     * @param $traceConfig
     *  execute
     *  setting
     *   logger_type
     *   host
     *   file_path
     *   file_name
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
     *  execute
     *  setting
     *   logger_type
     *   host
     *   file_path
     *   file_name
     * @return array|bool
     */
    public static function getTraceHeader($traceConfig)
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

    public static function toSpanName($service, $method)
    {
        return sprintf("%s_%s", $service, $method);
    }
}