<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\DeptModel;
use app\common\model\MenuModel;
use app\common\model\RoleMenuModel;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use app\common\model\UserRoleModel;
use think\Validate;

//个人中心
class Ucenter extends Base
{
    // 获取用户信息
    public function getInfo()
    {
        $user_info = $this->user_info;
        $uid = $user_info->uid;
        
        $UserModel = new UserModel();
        $fields = 'id,account,nickname,sex,mobile,email,head_url,dept_id';
        $user = $UserModel->where('id', '=', $uid)->field($fields)->find();
        if (empty($user)) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }

        $data = $user;
        //描述
        $data['description'] = $user->meta('description');
        //部门
        $user['dept'] = DeptModel::where('id', $user['dept_id'])->field('id,name')->find();
        unset($user['dept_id']);
        //查询角色
        $roleIds = UserRoleModel::where(['uid' => $user['id']])->column('role_id');
        $RoleModel = new RoleModel();
        $data['roles'] = $RoleModel->where('id', 'in', $roleIds)->field('id,name,title')->select()->toArray();

        $returnData = parse_fields($data->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
    
    //编辑个人资料
    public function profile()
    {
        $userInfo = $this->user_info;
        $uid = $userInfo->uid;
        $user = UserModel::get($uid);

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
        $data = $UserModel->where('id', $uid)->field('id,nickname,head_url')->find();
        $roleIds = UserRoleModel::where(['uid'=> $uid])->column('role_id');

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
        $uid = $userInfo->uid;
        $user = UserModel::get($uid);

        if (!$user) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '用户不存在!');
        }
        $roleIds = UserRoleModel::where(['uid'=> $uid])->column('role_id');

        $RolemenuModel = new RoleMenuModel();
        $menuIds = $RolemenuModel->where('role_id', 'in', $roleIds)->column('menu_id');

        $where[] = [
            ['belongs_to', '=', 'api'],
            ['id', 'in', $menuIds]
        ];
        $MenuModel = new MenuModel();
        $fields = 'id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to';
        $list = $MenuModel->where($where)->field($fields)->select();

        $list = parse_fields($list->toArray(), 1);
        $returnData = getTree($list, 0 , 'id', 'pid', 6);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //修改密码
    public function modifyPassword()
    {
        $params = $this->request->put();
        $validate = Validate::make([
            'oldPassword' => 'require',
            'password' => 'require|length:6,20|alphaDash'
        ]);
       
        if (!$validate->check($params)) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, $validate->getError());
        }

        $oldPassword = $params['oldPassword'];
        $oldPassword = encrypt_password($oldPassword, get_config('password_key'));

        $uid = $this->user_info;
        $uid = $uid->uid;
        $uid = 11;
        $user = UserModel::get($uid);
        if($user['password'] !== $oldPassword) {
            return ajax_error(ResultCode::E_PARAM_ERROR, '旧密码不正确');
        }
        
        $password = encrypt_password($params['password'], get_config('password_key'));
        $res = $user->isUpdate(true)->save(['password' => $password]);
        if (!$res) {
            return ajax_error(ResultCode::E_DB_ERROR, '修改失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', '');
    }
}