<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\logic\UserLogic;
use app\common\model\DeptModel;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use app\common\model\UserRoleModel;
use think\facade\Cache;
use think\facade\Validate;

class User extends Base
{

    // 获取用户信息
    public function query($id)
    {
        $fields = 'id,account,nickname,sex,mobile,email,status,head_url,qq,weixin,dept_id,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $user = $UserModel->where('id', $id)->field($fields)->find();

        if (empty($user)) {
            return ajax_return(ResultCode::E_USER_NOT_EXIST, '用户不存在');
        }
        
        $user['dept'] = DeptModel::where('id', $user['dept_id'])->field('id,name')->find();
        $roleIds = UserRoleModel::where('uid', '=', $user['id'])->column('role_id');
        $user['role'] = RoleModel::where('id', 'in', $roleIds)->field('id,name,title')->select();
        unset($user['dept_id']);
        $returnData = parse_fields($user->toArray(), 1);

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
        foreach ($filters as $key => $value) {
            if ($key ===  'status') {
                $where[] = ['status', '=', $value];
                continue;
            } elseif ($value == '') {
                continue;
            } else {
                $where[] = [$key, 'like', '%' . $value . '%'];
            }
        }
        $fields = 'id,account,nickname,sex,mobile,email,head_url,qq,weixin,dept_id,referee,status,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page' =>$page]);

        //查询部门和角色
        $DeptModel = new DeptModel();
        foreach ($list as $user) {
            $user['dept'] = $DeptModel->where('id', $user['dept_id'])->field('id,name,title')->find();
            $roleIds = UserRoleModel::where('uid', '=', $user['id'])->column('role_id');
            $user['role'] = RoleModel::where('id', 'in', $roleIds)->field('id,name,title')->select()->toArray();
            unset($user['dept_id']);
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
        $uid = $UserLogic->createUser($params['mobile'], $params['password'], $params['nickname'], $params['email'], '', $params['deptId']);
        
        if (!$uid) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        }
     
        if (!empty($params['roleIds'])) {
            $group = [];
            foreach ($params['roleIds'] as $k => $v) {
                $group[] = [
                    'uid' => $uid,
                    'role_id' => $v
                ];
            }
            $UserRoleModel = new UserRoleModel();
            $UserRoleModel->insertAll($group);
        }

        $UserModel = new UserModel();
        $fields = 'id,account,nickname,sex,mobile,email,head_url,qq,weixin,dept_id,referee,status,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $user = $UserModel->where('id', '=', $uid)->field($fields)->find();
        $user['dept'] = DeptModel::where('id', '=', $user['dept_id'])->field('id,name,title')->select();
        $returnData = parse_fields($user->toArray(), 1);

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
        if (isset($params['deptId'])) {
            $user->dept_id = $params['deptId'];
        }
        $res = $user->save();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_SUCCESS, '操作失败!');
        }

        //修改对应角色
        $UserRoleModel = new UserRoleModel();
        $UserRoleModel->where(['uid'=>$uid])->delete();
       
        if (!empty($params['roleIds'])) {
            $data = [];
            foreach ($params['roleIds'] as $k => $v) {
                $data[] = [
                    'uid'=>$uid,
                    'role_id'=>$v
                ];
            }
            $UserRoleModel->insertAll($data);
        }
        Cache::tag('menu')->rm($uid); //删除用户菜单配置缓存

        //返回数据
        $returnData = $user;
        $returnData['roleIds'] = $UserRoleModel->where('uid', $uid)->column('role_id');
        $returnData['dept'] = DeptModel::get($user['dept_id']);
        unset($returnData['dept_id']);

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
        $UserRoleModel = new UserRoleModel();
        $returnData['roleIds'] = $UserRoleModel->where('uid', $uid)->column('group_id');

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
        $UserRoleModel = new UserRoleModel();
        $UserRoleModel->where(['uid'=>$uid])->delete();
        if (!empty($params['roleIds'])) {
            $data = [];
            foreach ($params['roleIds'] as $k => $v) {
                $data[] = [
                    'uid'=>$uid,
                    'role_id'=>$v
                ];
            }
            $UserRoleModel->insertAll($data);
        }
        Cache::tag('menu')->rm($uid); //删除用户菜单配置缓存

        //返回数据
        $UserModel = new UserModel();
        $returnData = $UserModel->where('id', $uid)->field('id,nickname')->find();

        $RoleModel = new RoleModel();
        $returnData['roleIds'] = $RoleModel->alias('r')->join('sys_user_role u', 'r.id = u.role_id')->where('uid', $uid)->field('r.id,r.name')->select();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}
