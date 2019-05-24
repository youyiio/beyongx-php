@ECHO OFF

rem php -r "$r = (include 'config\queue.php');echo $r['default'];"; and return value;
for /f %%i in ('php -r "$r = (include 'config\queue.php');echo $r['default'];"') do ( set res=%%i)

set queue=%res%
set delay=2
set memory=128
set sleep=3
set tries=2
set timeout=600

php think queue:listen --queue %queue% --delay %delay% --memory %memory% --sleep %sleep% --tries %tries% --timeout %timeout%