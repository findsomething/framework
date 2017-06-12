<?php

return [
    'bootstrap' => dirname(__DIR__)."/bootstrap/bootstrap-test.php",
    'host' => '127.0.0.1',
    'port' => '9512',
    'pid_file' => dirname(__DIR__)."/var/locks/redis.pid",
    'server' => "FSth\\Framework\\NoSql\\Redis\\Pool",
    'setting' => [
        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'timeout' => 2,
            'num' => 100
        ],
        'server' => [
            'worker_num' => 8,
            'task_worker_num' => 20,
        ]
    ]
];