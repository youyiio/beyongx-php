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

        $config = [
            'login_success_view' => url('admin/Index/index'),
            'logout_success_view' => url('admin/Sign/index'),
            'register_enable' => false,
            'register_code_type' => 'mail',
            'reset_enable' => false,
            'reset_code_type' => 'mail',
        ];

        $this->defaultConfig = array_merge($this->defaultConfig, $config);

        $this->view->engine->layout(false);
    }

}
