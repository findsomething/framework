<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 16:46
 */
namespace FSth\Framework\Context;

abstract class Boot
{
    protected $params;

    protected $method;
    protected $config;

    protected $validMethod;

    public function __construct($params)
    {
        $this->params = $params;
        $this->validMethod = array('run', 'start', 'stop', 'restart');
    }

    public function handle()
    {
        $this->init();
        call_user_func_array(array($this, $this->method), array());
    }

    abstract function start($daemon = true);

    abstract function stop();

    protected function show()
    {
        echo "Usage: php {$this->params[0]} run|start|stop|restart pathToConfig\n";
    }

    protected function init()
    {
        if (count($this->params) != 3) {
            $this->method = "show";
            return;
        }
        $this->method = $this->params[1];
        if (!in_array($this->method, $this->validMethod)) {
            $this->method = "show";
            return;
        }

        $configFile = $this->params[2];
        if (!file_exists($configFile)) {
            $this->method = "show";
            return;
        }

        $this->beforeInit();

        $this->config = include $configFile;
    }

    protected function beforeInit()
    {

    }

    protected function run()
    {
        $this->start(false);
    }

    protected function restart()
    {
        $this->stop();
        sleep(1);
        $this->start(true);
    }
}