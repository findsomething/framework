# a frame work combine swoole & phalcon

## usage
   
bin/server start config/server.php 

wget http://127.0.0.1:9501 

look the demo for detail

## benchmark

### environment
* ubuntu 14.04
* 8 core 
* 24 G
* 20 workers

### test

* php run.php -c 100 -n 100000 -s tcp://127.0.0.1:9511 -f long_tcp
![](doc/tcp-benchmark.png)

* ab -n 100000 -c 100 http://127.0.0.1:9510/
![](doc/http-benchmark.png)

## changelog 

### 2017-01-17 v0.1.3
```
fix autoload
```

### 2017-02-22 v0.1.4
```
fix setting
```

### 2017-03-16 v0.1.5
```
add tcp service in multiServer
```

