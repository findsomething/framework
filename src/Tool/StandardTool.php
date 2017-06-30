<?php

namespace FSth\Framework\Tool;

class StandardTool
{
    public static function toError($error, $code, $traceId = '')
    {
        if (empty($traceId)) {
            $traceId = time() . '_' . substr(hash('md5', uniqid('', true)), 0, 10);
        }
        return [
            'error' => [
                'code' => $code,
                'message' => $error,
                'traceId' => $traceId
            ]
        ];
    }
}