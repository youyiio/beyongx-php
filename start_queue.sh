#!/bin/sh

queue=cms:queue
delay=2
memory=128
sleep=3
tries=2
timeout=600

nohup php think queue:listen --queue $queue --delay $delay --memory $memory --sleep $sleep --tries $tries --timeout $timeout 2>&1 &