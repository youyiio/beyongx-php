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
        
        $params = $_REQUEST;
        if (isset($params['password'])) {
            $params['password'] = '********';
        }

        $remark = "浏览";

        //登录日志
        $actionLog = new ActionLogLogic();
        $actionLog->addLog(0, ActionLogModel::ACTION_ACCESS, $remark, $params);
    }
}