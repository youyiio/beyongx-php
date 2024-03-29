<?php
/**
 * Created by VSCode.
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
        $httpReferer = Request::header('http-referer');
        $params = $_REQUEST;
        if (isset($params['password'])) {
            $params['password'] = '********';
        }

        if (strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }
        if (strlen($httpReferer) > 255) {
            $httpReferer = substr($httpReferer, 0, 255);
        }

        $remark = '';
        //登录日志
        $actionLog = new ActionLogLogic();
        $actionLog->addLog(0, ActionLogModel::ACTION_ACCESS, $remark, $params, $userAgent, $httpReferer);
    }
}