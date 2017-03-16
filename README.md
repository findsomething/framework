# a frame work combine swoole & phalcon

## usage
   
php bin/server start config/server.php 

wget http://127.0.0.1:9501 

see the demo for detail

## benchmark

ab -n 10000 -c 500 http://127.0.0.1:9501/
![swoole](https://github.com/findsomething/framework/blob/master/doc/pics/C44ED1B8-6D90-4015-86C4-D7873CC50088.png)

ab -n 10000 -c 500 http://127.0.0.1:9502/  for access phalcon directly
![normal](https://github.com/findsomething/framework/blob/master/doc/pics/94EAE332-44BF-4909-99E1-A1227937C745.png)

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