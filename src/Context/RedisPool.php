<?php

namespace FSth\Framework\Context;

class RedisPool extends Boot
{
    function start($daemon = true)
    {
        // TODO: Implement start() method.
        if (file_exists($this->config['pid_file'])) {
            echo "redis pool is already start \n";
            return;
        }
        echo "redis pool is starting...\n";
        $kernel = include $this->config['bootstrap'];
        $pool = new $this->config['server']($this->config['host'], $this->config['port'], $this->config['setting']);
        $pool->setLogger($kernel['logger']);

        if ($daemon) {
            $pool->daemonize();
        }

        echo "redis pool {$this->config['host']}:{$this->config['port']} started.\n";
        $pool->listen();
    }

    function stop()
    {
        // TODO: Implement stop() method.
        if (file_exists($this->config['pid_file'])) {
            $pid = intval(file_get_contents($this->config['pid_file']));
            if ($pid && posix_kill($pid, SIGTERM)) {
                while (1) {
                    if (file_exists($this->config['pid_file'])) {
                        sleep(1);
                        continue;
                    }

                    echo "redis pool has stopped.\n";
                    break;
                }
            }
        }
    }
}