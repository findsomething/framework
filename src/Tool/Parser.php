<?php

namespace FSth\Framework\Tool;

use FSth\Framework\Exception\FsException;

class Parser
{
    public function parse($result)
    {
        if (!is_array($result) || !ArrayTool::requireds($result, ['i', 's', 'r', 'o', 'e'])) {
            throw new FsException("返回内容格式错误");
        }
        if (!empty($result['e'])) {
            throw new FsException($result['e']['message'], $result['e']['code']);
        }
        return $result['r'];
    }
}