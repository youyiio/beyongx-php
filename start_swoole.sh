#!/bin/sh

#at linux, $r variable should be wrapped in ''; because $r is not shell variable in '';
host=`php -r 'require __DIR__ . "/thinkphp/base.php";$r = (include "config/swoole.php");echo $r["host"];'`
port=`php -r 'require __DIR__ . "/thinkphp/base.php";$r = (include "config/swoole.php");echo $r["port"];'`

echo "start swoole at $host:$port"
php think swoole stop

sleep 3s

chown apache:apache data/runtime -R

#php think swoole start
nohup php think swoole start &

lsof -i:$port