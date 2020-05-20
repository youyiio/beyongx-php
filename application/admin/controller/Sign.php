<?php
namespace app\admin\controller;

use app\common\logic\UserLogic;
use app\common\logic\ActionLogLogic;
use app\common\model\ActionLogModel;
use app\common\model\UserModel;
use think\facade\Cache;
use think\Controller;
use think\facade\Session;

/**
* 登录控制器
*/
class Sign extends \app\common\controller\Sign
{
    public function initialize()
    {
        parent::initialize();
    }

}
