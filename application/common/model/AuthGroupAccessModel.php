<?php
namespace app\common\model;

use think\Model;
/**
 * 权限规则model
 */
class AuthGroupAccessModel extends BaseModel
{
    protected $name = 'sys_auth_group_access';

	/**
	 * 根据group_id获取全部用户id
	 * @param  int $group_id 用户组id
	 * @return array         用户数组
	 */
	public function getUidsByGroupId($group_id)
    {
		$uids = $this->where(['group_id' => $group_id])->column('uid');
		return $uids;
	}

}
