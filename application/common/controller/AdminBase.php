<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-05-10
 * Time: 17:08
 */
namespace app\common\controller;


/**
 * Trait 管理后台Base Controller 组件
 * @package app\common\controller
 */
trait AdminBase
{

    /**
     * 检测登录session
     */
    protected function check()
    {
        //判断登陆session('uid')
        $uid = session('uid');
        if (!$uid) {
            if (request()->isAjax()) {
                $this->error('请重新登陆', url('cms/Sign/index'));
            }
            $this->redirect('admin/Sign/index');
        }

        //实现用户单个端登录，方法: 通过判断cookie和服务器cache的login_hash值
        $localLoginHash = cookie($uid . '_login_hash');
        $cacheLoginHash = cache($uid . '_login_hash');
        if ($localLoginHash != $cacheLoginHash) {
            $this->error('请重新登陆', url('admin/Sign/index'));
        }

        //用户有请求操作时，session时间重置
        $expire = config('session.expire');//缓存期限
        session('uid', $uid);
        cookie('uid', $uid, $expire);

        //权限验证
        if (config('cms.auth_on') == 'on') {
            $node = request()->module().'/'.request()->controller().'/'.request()->action();

            $auth = \think\auth\Auth::instance();
            if (!$auth->check($node, $uid)) {
                $this->error('没有访问权限', 'javascript:void(0);');
            }
        }
    }
}