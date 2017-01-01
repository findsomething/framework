<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/1
 * Time: 14:50
 */
include dirname(__DIR__) . '/vendor/autoload.php';

$conf = include dirname(__DIR__)."/config/parameters.php";

$kernel = new FSth\Framework\Context\Kernel($conf);
$kernel->boot();

return $kernel;