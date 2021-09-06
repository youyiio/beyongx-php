<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2017-06-20
 * Time: 14:59
 */

namespace app\common\behavior;

use think\facade\Request;
use think\facade\Log;

class LogBehavior
{

    public function run()
    {
        $baseUrl = Request::baseUrl();
        $ip = Request::ip(0, true);

        $datetime = "[" . date_time() . ' ' . (millisecond() % 1000) . "] ";
        $params = $_REQUEST;
        //不输出登陆密码
        if (isset($params['password'])) {
            $params['password'] = '********';
        }
        Log::info( $datetime . 'ip=' . $ip . '|path=' . $baseUrl . '|params=' . json_encode($params));
    }
}