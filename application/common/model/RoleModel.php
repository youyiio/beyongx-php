<?php
namespace app\common\model;

use think\Model;
/**
 * 权限规则model
 */
class RoleModel extends BaseModel
{
    protected $name = 'sys_role';

	const STATUS_DELETED = -1; //删除
	const STATUS_ACTIVED = 1; //激活
	const STATUS_FREEZED = 2; //冻结
	


	//关联menu表
	public function menus()
	{
		return $this->belongsToMany('MenuModel', config('database.prefix') . 'sys_role_menu', 'menu_id', 'role_id');
	}

	//关联中间表 roleMenuModel
	public function roleMenus()
	{
		return $this->hasMany('RoleMenuModel', 'role_id', 'id');
	}

	/**
	 * 传递主键id删除数据
	 * @param  array   $map  主键id
	 * @return boolean       操作是否成功
     * @throws
	 */
	public function deleteData($map)
    {
		$this->where($map)->delete();
		$group_map=[
			'menu_id'=>$map['id']
		];
		// 删除关联表中的组数据
        $UserRoleModel = new UserRoleModel();
		$result = $UserRoleModel->deleteData($group_map);
		return $result;
	}



}
