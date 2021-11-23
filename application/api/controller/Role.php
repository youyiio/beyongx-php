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
        $page = $params['page'];
        $size = $params['size'];

        $filters = $params['filters'];
        $keyword = $filters['keyword']?? '';

        $where = [];
        $fields = 'id,name,title,status,remark,create_by,update_by,create_time,update_time';
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%'.$keyword.'%'];
        }

        $RoleModel = new RoleModel();
        $list = $RoleModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page])->toArray();

        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

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

        $RoleModel = new RoleModel();
        $result = $RoleModel->save($params);

        if (!$result) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }
        $id = $RoleModel->id;
        $returnData = $RoleModel->where('id', '=' ,$id)->find();

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
        
        $res = $role->save($params);
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $role);
    }

    //删除角色
    public function delete($id)
    {
        //删除role表中的数据
        $role = RoleModel::get($id);
        $res = $role->delete();
        
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        //删除RoleMenu表中的数据
        $UserRoleModel = new UserRoleModel();
        $UserRoleModel->where('role_id', '=', $id)->delete();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查询角色权限
    public function menus($id)
    {
        $MenuModel = new RoleMenuModel();
        $menus = $MenuModel->where('role_id', $id)->column('menu_id');
     
        $ids = implode(',', $menus);
        $MenuModel = new MenuModel();
        $list = $MenuModel->where('id', 'in', $ids)->select();
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
    }

    //分配角色权限
    public function addMenus($id)
    {
        $params = $this->request->put();
        $menuIds = $params['menuIds']?? [];

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
        $page = $params['page']?? '1';
        $size = $params['size']?? '10';
        $filters = $params['filters'];
        $keyword = $filters['keyword'];
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