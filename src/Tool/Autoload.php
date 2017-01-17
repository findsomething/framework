<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/17
 * Time: 10:13
 */
return function () {
    $files = array(
        dirname(__DIR__)."/../vendor/autoload.php",
        dirname(__DIR__)."/../../../autoload.php",
    );

    foreach ($files as $file) {
        if (is_file($file)){
            require_once $file;
            return true;
        }
    }
    return false;
};