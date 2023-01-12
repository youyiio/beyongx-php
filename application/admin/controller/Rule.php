<?php
namespace app\admin\controller;

use app\common\model\MenuModel;
use app\common\model\RoleMenuModel;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use app\common\model\UserRoleModel;
use think\facade\Cache;

/**
 * 权限管理控制器
 */
class Rule extends Base
{

    /**
     * 权限列表
     */
    public function index()
    {
        $MenuModel = new MenuModel();
        $data = $MenuModel->getTreeDataBelongsTo('tree', 'id','path', 'id', 'pid', 'admin');

        $this->assign('data', $data);
        return $this->fetch('index');
    }

    /**
     * 添加权限
     */
    public function add()
    {
        $data = input('post.');
        unset($data['id']);

        //验证规则唯一性
        $rule = [
            'path|权限规则' => 'require|unique:'. config('database.prefix') . 'sys_menu,path',
        ];
        $check = $this->validate($data,$rule);
        if ($check !== true) {
            $this->error($check);
        }

        $data['belongs_to'] = 'admin';
        $data['is_menu'] = 1;

        $MenuModel = new MenuModel();
        $result = $MenuModel->save($data);
        if ($result) {
            $this->success('添加成功', url('Rule/index'));
        }else{
            $this->error('添加失败');
        }
    }

    /**
     * 修改权限
     */
    public function edit()
    {
        $data = input('post.');
        $map = [
            'id' => $data['id']
        ];
        $MenuModel = new MenuModel();
        $result = $MenuModel->editData($map, $data);
        if ($result) {
            $this->success('修改成功', url('Rule/index'));
        } else {
            $this->error('修改失败');
        }
    }

    /**
     * 删除权限
     */
    public function delete()
    {
        $id = input('param.id');
        $map = [
            'id' => $id
        ];
        $MenuModel = new MenuModel();
        $result = $MenuModel->deleteData($map);
        if ($result) {
            $this->success('删除成功', url('Rule/index'));
        } else {
            $this->error('请先删除子权限');
        }

    }

    /**
     * 菜单排序
     */
    public function order()
    {
        $data = input('post.');
        $arr = [];
        foreach ($data as $k => $v) {
            $arr[] = [
                'id' => $k,
                'sort' => empty($v) && $v !== '0' ? null : $v
            ];
        }
        $MenuModel = new MenuModel();
        $result = $MenuModel->isUpdate(true)->saveAll($arr);
        if ($result) {
            $this->success('排序成功', url('Rule/index'));
        } else {
            $this->error('排序失败');
        }
    }

    /**
     * 设置菜单值
     */
    public function setMenu()
    {
        $id = input('id/d', 0);
        $isMenu = input('is_menu/s', 'false');
        $isMenu = $isMenu === 'true' ? true : false;

        $MenuModel = new MenuModel();
        $result = $MenuModel->isUpdate(true)->save(['id' => $id, 'is_menu' => $isMenu]);
        if ($result) {
            $this->success('修改成功', url('Rule/index'));
        } else {
            $this->error('修改失败');
        }
    }

//*******************用户组**********************
    /**
     * 用户组列表
     */
    public function group()
    {
        $data = RoleModel::all();
        $this->assign('data', $data);
        return view('group');
    }

    /**
     * 添加用户组
     */
    public function addGroup()
    {
        $data = input('post.');
        unset($data['id']);

        $RoleModel = new RoleModel();
        $result = $RoleModel->save($data);
        if ($result) {
            $this->success('添加成功', url('Rule/group'));
        }else{
            $this->error('添加失败');
        }
    }

    /**
     * 修改用户组
     */
    public function editGroup()
    {
        $data = input('post.');
        $map=[
            'id'=>$data['id']
        ];

        $RoleModel = new RoleModel();
        $result = $RoleModel->editData($map,$data);
        if ($result) {
            $this->success('修改成功',url('Rule/group'));
        }else{
            $this->error('修改失败');
        }
    }

    /**
     * 删除用户组
     */
    public function deleteGroup(){
        $id = input('param.id');
        $map = [
            'id' => $id
        ];
        $RoleModel = new RoleModel();
        $result = $RoleModel->deleteData($map);
        if ($result !== false) {
            $this->success('删除成功', url('Rule/group'));
        }else{
            $this->error('删除失败');
        }
    }

//*****************权限-用户组*****************
    /**
     * 分配权限
     */
    public function ruleGroup()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $roleId = $data['id'];
           
            $RoleMenuModel = new RoleMenuModel();
            $RoleMenuModel->where('role_id', $roleId)->delete();
            $group = [];
            
            foreach ($data['rule_ids'] as $menuId) {
                $group[] = [
                    'role_id' => $roleId,
                    'menu_id'  => $menuId
                ];
            }
            $result = $RoleMenuModel->insertAll($group);

            if ($result !== false) {
                $UserRoleModel = new UserRoleModel();
                $groupUserIds = $UserRoleModel->where('role_id', $roleId)->column('uid');
                foreach ($groupUserIds as $uid) {
                    Cache::tag('menu')->rm($uid);
                }
                $this->success('操作成功', url('Rule/group'));
            } else {
                $this->error('操作失败');
            }
        }

        $id = input('param.id');
        // 获取用户组数据
        $RoleModel = new RoleModel();
        $roleData = $RoleModel->where('id', $id)->find();
        $roleData['rules'] = MenuModel::hasWhere('roleMenus', [['role_id', '=', $id]])->where('belongs_to', '=', 'admin')->column('sys_menu.id');

        // 获取规则数据
        $MenuModel = new MenuModel();
        $menu = $MenuModel->where('belongs_to','admin')->select();
        $tree = new \beyong\commons\data\Tree();
        $ruleData = $tree::channelLevel($menu, 0, '&nbsp;', 'id');
        
        // 分组信息
        $roles = $RoleModel->field('id, title')->select();
        $assign = [
            'group_data' => $roleData,
            'rule_data' => $ruleData,
            'groups' => $roles
        ];
        $this->assign($assign);

        return $this->fetch('ruleGroup');
    }

    //******************用户-用户组*******************
    /**
     * 添加成员
     */
    public function checkUser()
    {
        $groupId = input('param.role_id');
        $RoleModel = new RoleModel();
        $groupName = $RoleModel->where('id', $groupId)->value('title');
        $UserRoleModel = new UserRoleModel();
        $uids = $UserRoleModel->where('role_id', $groupId)->column('uid');

        if (request()->isAjax()) {
            $username = input('param.username', '');
            // 判断用户名是否为空
            if (empty($username)) {
                $userList = '';
            } else {
                $UserModel = new UserModel();
                $userList = $UserModel->where('mobile|email','like',"%$username%")->field('id,mobile,email')->select();
            }
            if (empty($userList)) {
                $this->error('未找到相关用户');
            }

            foreach ($userList as $k => $user) {
                if (in_array($user['id'], $uids)) {
                    $userList[$k]['isInGroup'] = 1;
                } else {
                    $userList[$k]['isInGroup'] = 0;
                    $userList[$k]['setUrl'] = url('Rule/addUserToGroup', ['uid'=>$user['id'], 'role_id'=>$groupId, 'username'=>$user['mobile']]);
                }
            }

            $this->success('找到的相关用户',null,$userList);
        }

        $assign = [
            'group_name' => $groupName,
            'uids'       => $uids
        ];
        $this->assign($assign);

        //当前分组成员
        $UserModel = new UserModel();
        $userList = $UserModel->where('id','in', $uids)->field('id,mobile,email,nickname')->select();
        $this->assign('userList',$userList);

        //未加入分组的用户
        $outUserList = $UserModel->where('id', 'not in', $uids)->field('id,mobile,email,nickname')->select();
        $this->assign('outUserList', $outUserList);

        return $this->fetch('checkUser');
    }

    /**
     * 添加用户到用户组
     */
    public function addUserToGroup()
    {
        $data = input('param.');
        $where = [
            'uid' => $data['uid'],
            'role_id' => $data['role_id']
        ];
        $UserRoleModel = new UserRoleModel();
        $count = $UserRoleModel->where($where)->count();
        if ($count == 0) {
            $res = $UserRoleModel->save($data);
            if ($res) {
                Cache::tag('menu')->rm($data['uid']);
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('已经是相关用户组了');
        }
    }

    /**
     * 将用户移除用户组
     */
    public function deleteUserFromGroup()
    {
        $data = input('param.');
        $where = [
            'uid' => $data['uid'],
            'role_id' => $data['role_id']
        ];
        $UserRoleModel = new UserRoleModel();
        $numRows = $UserRoleModel->where($where)->delete();
        if ($numRows >= 1) {
            Cache::tag('menu')->rm($data['uid']);
            $this->success('操作成功', url('Rule/userList'));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 管理员列表
     */
    public function userList()
    {
        $UserRoleModel = new UserRoleModel();
        $uids = $UserRoleModel->distinct(true)->column('uid');
        if (!empty($uids)) {
            $UserModel = new UserModel();
            $list = $UserModel->where('id', 'in', $uids)->paginate(20,false, ['query'=>input('param.')]);
            $this->assign(['data' => $list]);
            $this->assign('pages', $list->render());
        }

        return $this->fetch('userList');
    }

    /**
     * 添加管理员
     */
    public function addAdmin()
    {
        if(request()->isPost()){
            $data = input('post.');
            $data['user_type'] = 1;
            $userModel = new UserModel;
            $newUserId = $userModel->addUser($data);
            if($newUserId){
                if (!empty($data['role_ids'])) {
                    $group = [];
                    foreach ($data['role_ids'] as $k => $v) {
                        $group[] = [
                            'uid' => $newUserId,
                            'role_id' => $v
                        ];
                    }
                    $UserRoleModel = new UserRoleModel();
                    $UserRoleModel->insertAll($group);
                }
                Cache::tag('menu')->rm($newUserId);
                // 操作成功
                $this->success('添加成功', url('Rule/userList'));
            }else{
                // 操作失败
                $this->error($userModel->getError());
            }
        }

        $RoleModel = new RoleModel();
        $data = $RoleModel->select();
        $assign = [
            'data'=>$data
        ];
        $this->assign($assign);

        return $this->fetch('addAdmin');
    }

    /**
     * 修改管理员
     */
    public function editAdmin()
    {
        if (request()->isPost()) {
            $data = input('post.');
            // 组合where数组条件
            $uid = $data['uid'];

            // 修改权限
            $UserRoleModel = new UserRoleModel();
            $UserRoleModel->where(['uid'=>$uid])->delete();
            $group = [];
            foreach ($data['group_ids'] as $k => $v) {
                $group[] = [
                    'uid'=>$uid,
                    'role_id'=>$v
                ];
            }
            $UserRoleModel->insertAll($group);
            Cache::tag('menu')->rm($uid);

            $userModel = new UserModel();
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $user = UserModel::get($uid);
                $data['password'] = encrypt_password($data['password'], $user['salt']);
            }
            $result = $userModel->editUser($uid, $data);
            if ($result) {
                // 操作成功
                $this->success('编辑成功',url('Rule/editAdmin',['id'=>$uid]));
            } else {
                $errorMsg = $userModel->getError();
                if (empty($errorMsg)) {
                    $this->success('编辑成功',url('Rule/editAdmin',['id'=>$uid]));
                }else{
                    // 操作失败
                    $this->error($errorMsg);
                }

            }
        }

        $id = input('param.id/d',0);
        // 获取用户数据
        $user = UserModel::get($id);
        $this->assign('user', $user);

        //用户所属分组
        $UserRoleModel = new UserRoleModel();
        $userGroups = $UserRoleModel->where('uid', $id)->column('role_id');
        $this->assign('userGroups', $userGroups);

        //分组列表
        $RoleModel = new RoleModel();
        $groups = $RoleModel->select();
        $this->assign('groups', $groups);

        return $this->fetch('editAdmin');
    }
}
