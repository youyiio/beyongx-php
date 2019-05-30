@ECHO OFF

echo "deploy timer to schedule task for cms"

rem 此断代码之后，当前目录名称可直接引用 %~nx1
call :CurDIR "%cd%"
pause
goto :eof
:CurDIR
echo %~nx1

C:
cd \
%~d0
cd %~dp0

set current_path=%~dp0
set disk_c=C:\

set task_name=Beyongx_%~nx1
set start_file_name=starter.bat
set delay_time=0003:00


rem 删除已存在的%task_name%的计划任务
schtasks /delete /tn %task_name% /f

rem // 当前脚本路径为
echo "Current path: %~dp0 "

if exist %disk_c% (
  schtasks /create /ru SYSTEM /tn %task_name% /sc MINUTE /mo 1 /tr "cmd /c start /b php %current_path%think article:publish"
)
