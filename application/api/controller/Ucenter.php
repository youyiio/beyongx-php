<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\UserModel;

//个人中心
class Ucenter extends Base
{
    // 获取用户信息
    public function getInfo()
    {
        if ($this->request->method() != 'GET') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        $params = $this->request->put();
        
        $payloadData = session('jwt_payload_data');
        if (!$payloadData) {
            return ajax_error(ResultCode::ACTION_FAILED, 'TOKEN自定义参数不存在！');
        }
        $uid = $payloadData->uid;
        if (!$uid) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }
        $user = UserModel::get(['id' => $uid]);
        if (empty($user)) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }

        $returnData = [
            'uid' => $uid,
            //'account' => $user->account,
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            //'status' => $user->status,
            'headUrl' => $user->head_url,
            'sex' => $user->sex,
            'registerTime' => $user->register_time,
            'roles' => ['admin']
        ];

        return ajax_success($returnData);
    }
    
    //编辑个人资料
    public function profile()
    {
        $userInfo = $this->user_info;
        $user = UserModel::get($userInfo['uid']);

        if(!$user) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '用户不存在!');
        }

        $params = $this->params->put();
        
    }

    //查询权限菜单
    public function menus()
    {

    }
}