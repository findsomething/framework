<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/5
 * Time: 21:28
 */
namespace FSth\Framework\Tool;

use Phalcon\Mvc\Micro\Collection;

class Mount
{
    protected $prefix = '';
    protected $app;
    protected $path;

    public function __construct($app, $prefix = '')
    {
        $this->app = $app;
        $this->prefix = $prefix;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function mount($routeConfigs)
    {
        foreach ($routeConfigs as $name => $routes) {
            $collection = new Collection();
            $collection->setHandler($this->path."\\".$name, true);
            foreach($routes as $route){
                list($method, $url, $func) = $route;
                $collection->{strtolower($method)}($this->prefix.$url, $func);
            }
            $this->app->mount($collection);
        }
    }
}