<?php
return function() {
    $filePaths = [
        dirname(__DIR__)."/../../../../",
        dirname(__DIR__)."/../"
    ];

    foreach($filePaths as $path) {
        $fileName = $path.".env";
        if (is_file($fileName)){
            $dotenv = new \Dotenv\Dotenv($path);
            $dotenv->load();
            Env::init();
            echo "read .env file success\n";
            return true;
        }
    }
    return false;
};