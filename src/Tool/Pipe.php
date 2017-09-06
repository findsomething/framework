<?php
namespace FSth\Framework\Tool;

use FSth\Framework\Tool\ArrayTool;

class Pipe
{
    private $server;

    private $workerNum;
    private $taskNum;

    private $ignoreId;

    public function __construct(\swoole_server $server)
    {
        $this->server = $server;
        $this->workerNum = $server->setting['worker_num'];
        $this->taskNum = $server->setting['task_worker_num'];
    }

    public function setIgnoreId($ignoreId)
    {
        $this->ignoreId = $ignoreId;
    }

    public function sendToWorker($message)
    {
        return $this->sendMessage($message, $this->getWorkerId());
    }

    public function sendToTask($message)
    {
        return $this->sendMessage($message, $this->getTaskId());
    }

    private function sendMessage($message, $id)
    {
        return $this->server->sendMessage(ArrayTool::toString($message), $id);
    }

    private function getWorkerId()
    {
        while ($id = $this->getRawWorkerId()) {
            if ($id != $this->ignoreId) {
                return $id;
            }
        }
    }

    private function getTaskId()
    {
        while ($id = $this->getRawTaskId()) {
            if ($id != $this->ignoreId) {
                return $id;
            }
        }
    }

    private function getRawWorkerId()
    {
        return rand(0, 99999) % $this->workerNum;
    }

    private function getRawTaskId()
    {
        return (rand(0, 99999) % $this->taskNum) + $this->workerNum;
    }
}