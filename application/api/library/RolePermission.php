<?php
namespace app\api\library;

use app\common\model\UserRoleModel;
use app\common\model\RoleMenuModel;
use app\common\model\MenuModel;
use think\facade\Cache;

/**
 * RolePermission class
 * 角色权限检测类
 */
class RolePermission {

    public function __construct()
    {
    }

    public function checkPermission($uid, $permission) 
    {
        $permissions = Cache::get("permission" . CACHE_SEPARATOR . $uid, null);
        if ($permissions === null) {
            $permissions = $this->getPermissionList($uid);

            Cache::set("permission" . CACHE_SEPARATOR . $uid, $permissions, 3600);
        }

        if (!array_key_exists($permission, $permissions)) {
            return false;
        }

        return true;
    }

    //查询权限列表
    public function getPermissionList($uid) 
    {
        
        $roleIds = UserRoleModel::where(['uid'=> $uid])->column('role_id');

        $RolemenuModel = new RoleMenuModel();
        $menuIds = $RolemenuModel->where('role_id', 'in', $roleIds)->column('menu_id');

        $where[] = [
            ['belongs_to', '=', 'api'],
            ['id', 'in', $menuIds]
        ];
        $MenuModel = new MenuModel();
        //$fields = 'id,pid,title,name,component,path,icon,type,is_menu,permission,status,sort,belongs_to';
        $fields = 'id';

        $permissions = $MenuModel->where($where)->field($fields)->column($fields, "lower(permission)");

        return $permissions;
    }
}