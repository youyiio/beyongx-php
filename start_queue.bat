@ECHO OFF

set queue=cms:queue
set delay=2
set memory=128
set sleep=3
set tries=2
set timeout=600

php think queue:listen --queue %queue% --delay %delay% --memory %memory% --sleep %sleep% --tries %tries% --timeout %timeout%