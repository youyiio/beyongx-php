<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
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
        $keyword = $filters['keyword'];

        $where = [];
        $fields = 'id,name,title,status,remark,create_by,update_by,create_time,update_time';
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }

        $RoleModel = new RoleModel();
        $list = $RoleModel->where($where)->field($fields)->paginate($size, false, ['page' => $page]);
    
        //查询角色权限
        $MenuModel = new MenuModel();
        foreach ($list as $key => $value) {
            $list[$key]['menuIds'] = $MenuModel::hasWhere('roleMenus', [['role_id', '=', $value['id']]])->where('belongs_to', '=', 'api')->column('sys_menu.id');
          
        }
        $returnData = pagelist_to_hump($list);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增角色
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'name' => 'alphaDash',
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
        $id = $RoleModel->isUpdate(false)->allowField(true)->insertGetId($params);
        if (!$id) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $role = $RoleModel->where('id', '=', $id)->find();
        $returnData = parse_fields($role, 1);

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
            'name' => 'alphaDash',
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
        $RoleMenuModel = new RoleMenuModel();
        $ids = $RoleMenuModel->where('role_id', $id)->column('menu_id');
       
        $MenuModel = new MenuModel();
        $where = [
            ['id', 'in', $ids],
            ['belongs_to', '=', 'api']
        ];
        $fields = 'id,pid,title,name,component,path,icon,type,is_menu,status,sort,belongs_to,create_by,update_by,create_time,update_time';
        $list = $MenuModel->where($where)->field($fields)->select();

        $data = parse_fields($list->toArray(), 1);
        $returnData = getTree($data, 0, 'id', 'pid', 5);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //分配角色权限
    public function addMenus($id)
    {
        $params = $this->request->put();
        $menuIds = $params['menuIds'] ?? [];

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
    public function users($id)
    {
        $params = $this->request->put();
        $page = $params['page'] ?? '1';
        $size = $params['size'] ?? '10';
        $filters = $params['filters'];
        $keyword = $filters['keyword'] ?? '';
        $where = [];
        if (!empty($keyword)) {
            $where[] = ['nickname|mobile|email', 'like', '%' . $keyword . '%'];
        }

        $UserRoleModel = new UserRoleModel();
        $uids = $UserRoleModel->where('role_id', $id)->column('uid');

        //查找符合条件的用户
        $where[] = ['id', 'in', $uids];
        $fields = 'id,nickname,sex,mobile,email,head_url,qq,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page' => $page]);

        $returnData = pagelist_to_hump($list);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}