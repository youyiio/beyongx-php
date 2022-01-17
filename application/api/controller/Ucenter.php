<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\logic\UserLogic;
use app\common\model\DeptModel;
use app\common\model\JobModel;
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
        $user['dept'] = DeptModel::where('id', $user['dept_id'])->field('id,name,title')->find();
        unset($user['dept_id']);
        //角色
        $user['roles'] = RoleModel::hasWhere('UserRole', ['uid' => $user['id']], 'id,name,title')->select()->toArray();
        //岗位
        $user['jobs'] = JobModel::hasWhere('UserJob', ['uid' => $user['id']], 'id,name,title')->select()->toArray();

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
        $check = Validate('User')->scene('ucenterEdit')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('User')->getError());
        }

        if (isset($params['mobile']) && $user['mobile'] != $params['mobile']) {
            if ($user->findByMobile($params['mobile'])) {
                return ajax_return(ResultCode::E_USER_MOBILE_HAS_EXIST, '手机号已经存在!');
            }
        }
        if (isset($params['email']) && $user['email'] != $params['email']) {
            if ($user->findByEmail($params['email'])) {
                return ajax_return(ResultCode::E_USER_EMAIL_HAS_EXIST, '邮箱已经存在');
            }
        }

        $params = parse_fields($params);
        $user->nickname = $params['nickname'];
        $user->mobile = $params['mobile'];
        $user->email = $params['email'];
        $user->sex = $params['sex'];
        if (isset($params['qq'])) {
            $user->qq = $params['qq'];
        }
        if (isset($params['weixin'])) {
            $user->weixin = $params['weixin'];
        }
        if (isset($params['head_url'])) {
            $user->head_url = $params['head_url'];
        }
        $res = $user->save();
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }
        if (isset($params['description'])) {
            $user->meta('description', $params['description']);
        }   

        //返回数据
        $UserModel = new UserModel();
        $data = $UserModel->where('id', $uid)->field('id,nickname,head_url,mobile,email,qq,weixin,sex')->find();
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

        $roleIds = $user->roles->column('id');
        $field = 'id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to';
        $MenuModel = MenuModel::hasWhere('roleMenus', [['role_id', 'in', $roleIds]], $field)->group([]);

        $where[] = ['belongs_to', '=', 'api'];
        $list = $MenuModel->where($where)->select();
        
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