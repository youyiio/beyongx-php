@ECHO OFF

set swoole_path=E:\wnmp\swoole_4.4.9\bin
set swoole_path_set=false

echo %path% | find /i "%swoole_path%" && set swoole_path_set=true || set path=%swoole_path%;%path%
echo swoole env set: %swoole_path_set%

echo where php
where php
php --version

rem composer require topthink/think-swoole v2.0.17

php think swoole