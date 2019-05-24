<?php
namespace app\admin\controller;

use app\common\model\AuthRuleModel;
use think\Controller;
use think\facade\Cache;
use think\helper\Time;
use app\common\model\UserModel;

/**
* 基础控制器
*/
class Base extends Controller
{
    protected $uid;

    public function initialize()
    {
        //判断登陆session('uid')
        $uid = session('uid');
        if (!$uid) {
            if (request()->isAjax()) {
                $this->error('请重新登陆', url('cms/Sign/index'));
            }
            $this->redirect('admin/Sign/index');
        }
        $this->uid = $uid;

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

        //用户信息
        $myself = UserModel::get($uid);
        $this->assign('myself', $myself);

        //昨日新增用户
        $UserModel = new UserModel();
        $yesterdayNewUserCount = $UserModel->cache('yesterdayNewUserCount',time_left())->whereTime('register_time','between',Time::yesterday())->count();
        $this->assign('yesterdayNewUserCount', $yesterdayNewUserCount);

        //菜单数据,Cache::tag不支持redis
        if (Cache::has($uid . '_menu')) {
            $menus = Cache::get($uid . '_menu');
        } else {
            $AuthRuleModel = new AuthRuleModel();
            $menus = $AuthRuleModel->getTreeDataBelongto('level', 'sort, id', 'name', 'id', 'pid', 'admin');
            Cache::set($uid . '_menu', $menus);
        }

        $this->assign('menus', $menus);
    }

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return view('public/404');
    }
}
