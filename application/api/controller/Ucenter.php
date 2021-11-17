<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\MenuModel;
use app\common\model\RoleMenuModel;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use app\common\model\UserRoleModel;

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
        $user = UserModel::get($userInfo->uid);

        if (!$user) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '用户不存在!');
        }

        $params = $this->request->put();
        $res = $user->isUpdate(true)->allowField(true)->save($params);

        if (!$res) {
            ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }
        if (isset($params['description'])) {
            $user->meta('description', $params['description']);
        }   

        //返回数据
        $UserModel = new UserModel();
        $data = $UserModel->where('id', $userInfo->uid)->field('id,nickname,head_url')->find();
        $roleIds = UserRoleModel::where(['uid'=> $userInfo->uid])->column('role_id');

        $RoleModel = new RoleModel();
        $data['roles'] = $RoleModel->where('id', 'in', $roleIds)->field('id,name')->select();
        $data['description'] = $user->metas('description');
        $returnData = parse_fields($data->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
        
    }

    //查询权限菜单
    public function menus()
    {
        $userInfo = $this->user_info;
        $user = UserModel::get($userInfo->uid);

        if (!$user) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '用户不存在!');
        }
        $roleIds = UserRoleModel::where(['uid'=> $userInfo->uid])->column('role_id');

        $RolemenuModel = new RoleMenuModel();
        $menuIds = $RolemenuModel->where('role_id', 'in', $roleIds)->column('menu_id');
     
        $MenuModel = new MenuModel();
        $fields = 'id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to';
        $where[] = ['belongs_to', '=', 'api'];
        $where[] = ['id', 'in', $menuIds];
        $list = $MenuModel->where($where)->field($fields)->select();

        $list = parse_fields($list, 1);
        $returnData = getTree($list, 0 , 'id', 'pid', 6);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}