#!/bin/sh

#at linux, $r variable should be wrapped in ''; because $r is not shell variable in '';
queue=`php -r '$r = (include "config/queue.php");echo $r["default"];'`
delay=2
memory=128
sleep=3
tries=2
timeout=600

pid=`cat start_queue.pid`
echo "kill queue:work pid: $pid"
kill -9 $pid

echo "start $queue"
nohup php think queue:listen --queue $queue --delay $delay --memory $memory --sleep $sleep --tries $tries --timeout $timeout 2>&1 & echo $! > start_queue.pid

ps -aux | grep $queue