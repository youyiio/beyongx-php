<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\DeptModel;
use app\common\model\MenuModel;
use app\common\model\RoleMenuModel;
use app\common\model\RoleModel;
use app\common\model\UserModel;
use app\common\model\UserRoleModel;
use think\facade\Cache;
use think\Validate;

class Role extends Base
{
    public function list()
    {
        $params = $this->request->put();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $filters = $params['filters'];
        $keyword = $filters['keyword'] ?? '';

        $where = [];
        $fields = 'id,name,title,status,remark,create_by,update_by,create_time,update_time';
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%'.$keyword.'%'];
        }

        $RoleModel = new RoleModel();
        $list = $RoleModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page]);
        //查询角色权限
        $RoleMenuModel = new RoleMenuModel();
        foreach ($list as $key => $value) {
            $list[$key]['menuIds'] = $RoleMenuModel->where('role_id', '=', $value['id'])->column('menu_id');
        }
        $returnData = pagelist_to_hump($list);
       
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增角色
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'name' => 'alpha',
            'title' => 'chs',
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '参数错误!', $validate->getError());
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);
        $params['create_by'] = $userInfo['nickname'] ?? '';
        $params['update_by'] = $userInfo['nickname'] ?? '';
        $params['create_time'] = date_time();
        $params['update_time'] = date_time();

        $RoleModel = new RoleModel();
        $result = $RoleModel->isUpdate(false)->allowField(true)->save($params);
        if (!$result) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }
        $id = $RoleModel->id;

        $role = $RoleModel->where('id', '=' ,$id)->find();

        $returnData = parse_fields($role->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑角色
    public function edit()
    {
        $params = $this->request->put();

        //验证参数
        $id = $params['id'];
        $role = RoleModel::get($id);
        if (!$role) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '角色不存在!');
        }
        $validate = Validate::make([
            'name' => 'alpha',
            'title' => 'chs',
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '参数错误!', $validate->getError());
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);

        $params['update_by'] = $userInfo['nickname'] ?? '';
        $params['update_time'] = date_time();
        $res = $role->save($params);
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $returnData = parse_fields($role->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //删除角色
    public function delete($id)
    {
        $Role = RoleModel::get($id);
        if (!$Role) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '角色不存在!');
        }
        $res = $Role->save(['status' => RoleModel::STATUS_DELETED]);
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查询角色权限
    public function menus($id)
    {
        $MenuModel = new RoleMenuModel();
        $menus = $MenuModel->where('role_id', $id)->column('menu_id');
        $ids = implode(',', $menus);

        $MenuModel = new MenuModel();
        $fields = 'id,pid,title,name,component,path,icon,type,is_menu,status,sort,belongs_to,create_by,update_by,create_time,update_time';
        $list = $MenuModel->where('id', 'in', $ids)->field($fields)->select();

        $data = parse_fields($list->toArray() ,1);
        $returnData = getTree($data, 0, 'id', 'pid', 5);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //分配角色权限
    public function addMenus($id)
    {
        $params = $this->request->put();
        $menuIds = $params['menuIds']?? [];

        $role = RoleModel::get($id);
        if (!$role) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '角色不存在!');
        }
        $RoleMenuModel = new RoleMenuModel();
        $RoleMenuModel->where('role_id', $id)->delete();
     
        if (!empty($menuIds)) {
            $group = [];
            foreach ($menuIds as $menuId) {
                $group[] = [
                    'role_id' => $id,
                    'menu_id'  => $menuId
                ];
            }
           
            $RoleMenuModel->insertAll($group);
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查询角色用户列表
    public function userList($id)
    {
        $params = $this->request->put();
        $page = $params['page'] ?? '1';
        $size = $params['size'] ?? '10';
        $filters = $params['filters'];
        $keyword = $filters['keyword'] ?? '';
        $where = [];
        if (!empty($keyword)) {
            $where[] = ['nickname|mobile|email', 'like', '%'.$keyword.'%'];
        }

        $UserRoleModel = new UserRoleModel();
        $uids = $UserRoleModel->where('role_id', $id)->column('uid');
      
        //查找符合条件的用户
        $where[] = ['id', 'in', $uids];
        $fields = 'id,nickname,sex,mobile,email,head_url,qq,dept_id,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page]);

        $DeptModel = new DeptModel();
        foreach ($list as $val) {
            $val['dept'] = $DeptModel->where('id', $val['dept_id'])->field('id,name')->find();
            unset($val['dept_id']);
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
}