<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\logic\UserLogic;
use app\common\model\AuthGroupAccessModel;
use app\common\model\UserModel;
use think\facade\Cache;
use think\facade\Validate;

class User extends Base
{

    // 获取用户信息
    public function query($id)
    {
        $fields = 'id,account,nickname,sex,mobile,email,status,head_url,qq,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $user = $UserModel->where('id', $id)->field($fields)->find();

        if (empty($user)) {
            return ajax_return(ResultCode::E_USER_NOT_EXIST, '用户不存在');
        }
        
        if ($user['status'] !== UserModel::STATUS_ACTIVED) {
            if ($user['status'] == UserModel::STATUS_APPLY) {
                return ajax_return(ResultCode::E_USER_STATE_NOT_ACTIVED, '用户未激活');
            }
            if ($user['status'] == UserModel::STATUS_FREEZED) {
                return ajax_return(ResultCode::E_USER_STATE_FREED, '用户已冻结');
            }
            if ($user['status'] == UserModel::STATUS_DELETED) {
                return ajax_return(ResultCode::E_USER_STATE_DELETED, '用户已删除');
            }

            return ajax_return(ResultCode::E_UNKNOW_ERROR, '未知错误!');
        }

        $user['dept'] = [];
        unset($user['status']);
        $returnData = $user;
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //获取用户列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page'];
        $size = $params['size'];
        $filters = $params['filters'] ?? []; 

        $where = [];
        $fields = 'id,nickname,sex,mobile,email,head_url,qq,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        if (isset($filters['keyword'])) {
            $where[] = ['id|mobile|email|nickname', 'like', '%'.$filters['keyword'].'%'];
        }

        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page' =>$page]);

        //查询部门
        foreach ($list as $val) {
            $val['dept'] = [];
        }

        $list = $list->toArray();
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增用户
    public function create()
    {
        $params = $this->request->put();

        $check = Validate('User')->scene('create')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('User')->getError());
        }

        $UserLogic = new UserLogic();
        $uid = $UserLogic->createUser($params['mobile'], $params['password'], $params['nickname'], $params['email']);
        
        if (!$uid) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        }
     
        if (!empty($params['roleIds'])) {
            $group = [];
            foreach ($params['roleIds'] as $k => $v) {
                $group[] = [
                    'uid' => $uid,
                    'group_id' => $v
                ];
            }
            $AuthGroupAccessModel = new AuthGroupAccessModel();
            $AuthGroupAccessModel->insertAll($group);
        }

        $user = UserModel::get($uid);
        $returnData = parse_fields($user, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);  
    }

    //编辑用户
    public function edit()
    {
        $params = $this->request->put();

        $check = Validate('User')->scene('edit')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('User')->getError());
        }

        $uid = $params['id'];

        $user = UserModel::get($uid);
        $user->nickname = $params['nickname'];
        $user->mobile = $params['mobile'];
        $user->email = $params['email'];
        if (isset($params['qq'])) {
            $user->qq = $params['qq'];
        }
        if (isset($params['weixin'])) {
            $user->weixin = $params['weixin'];
        }
        $res = $user->save();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_SUCCESS, '操作失败!');
        }

        // 修改权限
        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $AuthGroupAccessModel->where(['uid'=>$uid])->delete();
       
        if (!empty($params['roleIds'])) {
            $group = [];
            foreach ($params['roleIds'] as $k => $v) {
                $group[] = [
                    'uid'=>$uid,
                    'group_id'=>$v
                ];
            }
            $AuthGroupAccessModel->insertAll($group);
        }
        Cache::tag('menu')->rm($uid); //删除用户菜单配置缓存

        //返回数据
        $returnData = $user;
        $returnData['roleIds'] = $AuthGroupAccessModel->where('uid', $uid)->column('group_id');

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //删除用户
    public function delete($id)
    {
        if ($id == 0) {
            $this->error('参数错误');
        }

        $res = UserModel::where('id', $id)->setField('status', UserModel::STATUS_DELETED);

        if (!$res) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '删除失败!');
        }

        //删除用户角色
        return ajax_return(ResultCode::ACTION_SUCCESS, '删除成功!');
    }

    //修改密码
    public function modifyPassword()
    {
        $params = $this->request->put();

        $check = Validate('User')->scene('modifyPassword')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('User')->getError());
        }

        $uid = $params['id'];
        $password = $params['password'];
        $newPassword = encrypt_password($password, get_config('password_key'));

        $data['id'] = $uid;
        $data['password'] = $newPassword;
        $UserModel = new UserModel();
        $UserModel->isUpdate(true)->save($data);

        //返回数据
        $returnData = UserModel::get($uid);
        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $returnData['roleIds'] = $AuthGroupAccessModel->where('uid', $uid)->column('group_id');

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //冻结用户
    public function freeze()
    {
        $params = $this->request->put();

        $uid = $params['id'];
        if ($uid == 0) {
            $this->error('参数id错误');
        }

        $UserModel = new UserModel();
        $res = $UserModel->where('id', $uid)->where('status', UserModel::STATUS_ACTIVED)->setField('status', UserModel::STATUS_FREEZED);

        if (!$res) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        } 

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //解冻用户
    public function unfreeze()
    {
        $params = $this->request->put();

        $uid = $params['id'];
        if ($uid == 0) {
            $this->error('参数id错误');
        }

        $UserModel = new UserModel();
        $res = $UserModel->where('id', $uid)->setField('status', UserModel::STATUS_ACTIVED);

        if (!$res) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        } 

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //用户分配角色
    public function addRoles()
    {
        $params = $this->request->put();

        $check = Validate('User')->scene('addRoles')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('User')->getError());
        }

        $uid = $params['id'];
        // 修改权限
        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $AuthGroupAccessModel->where(['uid'=>$uid])->delete();
        if (!empty($params['roleIds'])) {
            $group = [];
            foreach ($params['roleIds'] as $k => $v) {
                $group[] = [
                    'uid'=>$uid,
                    'group_id'=>$v
                ];
            }
            $AuthGroupAccessModel->insertAll($group);
        }
        Cache::tag('menu')->rm($uid); //删除用户菜单配置缓存

        //返回数据
        $returnData = UserModel::get($uid);
        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $returnData['roleIds'] = $AuthGroupAccessModel->where('uid', $uid)->column('group_id');

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}
