<?php
namespace app\admin\controller;

use app\common\logic\UserLogic;
use app\common\model\cms\ArticleModel;
use app\common\model\UserModel;

/**
* 个人中心控制器
*/
class Person extends Base
{

    //个人首页
    public function index()
    {
        //个人信息
        $uid = session('uid');
        $user = UserModel::get($uid);
        $this->assign('user', $user);

        //个人文章
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel->where('uid', $uid)->where('status','>=',0)->order('update_time desc')->paginate(20, false);
        $this->assign('articleList', $articleList);

        return $this->fetch('index');
    }

    //修改个人资料
    public function profile()
    {
        $uid = session('uid');

        if (request()->isAjax()) {
            $nickname = input('param.nickname');
            $qq = input('param.qq');
            $weixin = input('param.weixin');

            $UserModel = new UserModel();
            $res = $UserModel->setProfile($uid, $nickname, '', '', $qq, $weixin);
            if (!$res) {
                $this->error($UserModel->getError());
            }
            $this->success('修改成功');
        }


        $user = UserModel::get($uid);
        $this->assign('user', $user);

        return $this->fetch('profile');
    }

    //修改个人密码
    public function password()
    {

        if (request()->isAjax()) {
            $data = input('post.');
            //验证
            $validate = validate('User');
            $check = $validate->scene('modifyPassword')->check($data);
            if ($check !== true) {
                $this->error($validate->getError());
            }

            $oldPassword = input("param.password");
            $password = input("param.newPwd");

            $userId = session("uid");
            $userLogic = new UserLogic();
            $result = $userLogic->modifyPassword($userId, $oldPassword, $password);
            if (!$result) {
                $this->error("出错了!" );
            }

            $this->success('成功修改密码', url('admin/Sign/logout'));
        }

        return $this->fetch('password');
    }

}
