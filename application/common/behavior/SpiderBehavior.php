<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-06-05
 * Time: 10:38
 */

namespace app\common\behavior;

use app\common\model\ActionLogModel;
use think\facade\Request;

use app\common\logic\ActionLogLogic;

class SpiderBehavior
{
    public function run()
    {
        $userAgent = Request::header('user-agent');
        $params = $_REQUEST;
        if (isset($params['password'])) {
            $params['password'] = '********';
        }

        //登录日志
        $actionLog = new ActionLogLogic();
        $actionLog->addLog(0, ActionLogModel::ACTION_ACCESS, $userAgent, $params);
    }
}