<?php

namespace app\api\controller;

use app\api\controller\Base;

class Server extends Base
{

    public function status()
    {
        $use_status = $this->get_used_status();

        $data = [
            "sys" => [
                "os" => php_uname(),
                "day" => $use_status['sys']['uptime'], //$this->get_sys_uptime(),
                "ip" => GetHostByName($_SERVER['SERVER_NAME'])
            ],
            "cpu" => [
                "name" => "",
                "package" => "",
                "core" => "",
                "logic" => "",
                "used" => $use_status['cpu']['used'],
                "idle" => ""
            ],
            "memory" => [
                "total" => $use_status['mem']['total'],
                "available" => "",
                "used" => $use_status['mem']['used'],
                "usageRate" => $use_status['mem']['usageRate']
            ],
            "swap" => [
                "total" => "",
                "available" => "",
                "used" => "",
                "usageRate" => ""
            ], 
            "disk" => [
                "total" => $use_status['disk']['total'],
                "available" => $use_status['disk']['available'],
                "used" => $use_status['disk']['used'],
                "usageRate" => $use_status['disk']['usageRate']
            ],
            "time" => $use_status["time"]
        ];

        return ajax_success($data);
    }

    //系统启动时间
    protected function get_sys_uptime()
    {
        $uptime = "0小时";
        if (strtolower(PHP_OS) == "linux") {
            if (false === ($str = @file("/proc/uptime"))) {
                $uptime = "0小时";
            } else {
                $str = explode(" ", implode("", $str));
                $str = trim($str[0]);
                $min = $str / 60;
                $hours = $min / 60;
                $days = floor($hours / 24);
                $hours = floor($hours - ($days * 24));
                $min = floor($min - ($days * 60 * 24) - ($hours * 60));

                $uptime = "";
                if ($days !== 0) $uptime = $days."天";
                if ($hours !== 0) $uptime .= $hours."小时";
                $uptime .= $min . "分钟";
            }
        } else {
            if (strrpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'zh-cn') !== false) {
                $start_time = exec('systeminfo | find /i "启动时间"');
            } else {
                $start_time = exec('systeminfo | find /i "Boot Time"');
            }
            
            $uptime = $start_time;
        }
     
        return $uptime;
    }

    function get_used_status()
    {
        //获取某一时刻系统cpu和内存使用情况
        $fp = popen('top -b -n 1 | grep -E "^(top|Tasks|%Cpu|KiB Mem|KiB Swap)"',"r");
        $rs = "";
        while(!feof($fp)){
            $rs .= fread($fp, 1024);
        }
        pclose($fp);

        $sys_info = explode("\n", $rs);
        $top_info = explode(",", $sys_info[0]); //运行时间及负载情况
        $tast_info = explode(",", $sys_info[1]); //进程 数组        
        $cpu_info = explode(",", $sys_info[2]); //CPU占有量 数组
        $mem_info = explode(",", $sys_info[3]); //内存占有量 数组

        //运行时间
        $times = trim(trim($top_info[0], 'top -'));
        $times = explode("up", $times);
        $detection_time = trim($times[0]);
        $uptime = trim($times[1]);

        //正在运行的进程数
        $task_total = trim(trim($tast_info[1], 'Tasks:'), 'total');
        $tast_running = trim(trim($tast_info[1], 'running'));

        //CPU占有量
        $cpu_usage = trim(trim($cpu_info[0], 'Cpu(s): '), '%us'); //百分比
        
        //内存占有量
        $mem_total = trim(trim($mem_info[0], 'Mem: '),'k total');
        $mem_used = trim($mem_info[1], 'k used');
        $mem_usage = round(100 * intval($mem_used) / intval($mem_total), 2); //百分比
        
        /*硬盘使用率 begin*/        
        $fp = popen('df -lh | grep -E "^(/)"',"r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", ' ', $rs); //把多个空格换成 “_”
        $hd = explode(" ", $rs);
        $hd_total = trim($hd[1]);
        $hd_used = trim($hd[2]);
        $hd_avail = trim($hd[3], 'G'); //磁盘可用空间大小 单位G
        $hd_usage = trim($hd[4], '%'); //挂载点 百分比
        //print_r($hd);
        /*硬盘使用率 end*/

        /*获取IP地址 begin*/
        /*
        $fp = popen('ifconfig eth0 | grep -E "(inet addr)"','r');
        $rs = fread($fp,1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/",' ',trim($rs)); //把多个空格换成 “_”
        $rs = explode(" ",$rs);
        $ip = trim($rs[1],'addr:');
        */
        /*获取IP地址 end*/
        /*
        $file_name = "/tmp/data.txt"; // 绝对路径: homedata.dat
        $file_pointer = fopen($file_name, "a+"); // "w"是一种模式，详见后面
        fwrite($file_pointer,$ip); // 先把文件剪切为0字节大小， 然后写入
        fclose($file_pointer); // 结束
        */

        return [
            'sys' => [
                'uptime' => $uptime,
            ],
            'cpu' => [
                "usageRate" => $cpu_usage
            ],
            'mem' => [
                'total' => $mem_total,
                'used' => $mem_used,
                'useageRate' => $mem_usage
            ],
            'disk' => [
                'total' => $hd_total,
                'used' => $hd_used,
                'available' => $hd_avail,
                'useageRate' => $hd_usage
            ],
            'tast' => [
                'total' => $task_total,
                'running' => $tast_running
            ],
            'time' => $detection_time
        ];
    }
}