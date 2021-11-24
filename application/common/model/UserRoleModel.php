<?php
namespace app\common\model;

use think\Model;
/**
 * 权限规则model
 */
class UserRoleModel extends BaseModel
{
    protected $name = 'sys_user_role';

	/**
	 * 根据group_id获取全部用户id
	 * @param  int $group_id 用户组id
	 * @return array         用户数组
	 */
	public function getUidsByRoleId($role_id)
    {
		$uids = $this->where(['role_id' => $role_id])->column('uid');
		return $uids;
	}

}
