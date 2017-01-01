<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/8
 * Time: 13:45
 */
namespace FSth\Framework\Context;

use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

class Kernel extends Container
{
    protected $config;
    protected $putted;

    protected $cache_db;
    protected $cache_redis;

    public function __construct($config)
    {
        $this->config = $config;
        parent::__construct($config);
    }

    public function config($name, $default = null)
    {
        if (!isset($this->config[$name])) {
            return $default;
        }

        return $this->config[$name];
    }

    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function boot()
    {
        $this['logger'] = function ($kernel) {
            $logger = new Logger('run');
            $logger->pushHandler(new StreamHandler($this->config['log_path'].'run.log'));
            return $logger;
        };
        
        $this['namespace'] = $this->config['namespace'];
    }
}