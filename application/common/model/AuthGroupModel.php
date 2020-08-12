<?php
namespace app\common\model;

use think\Model;
/**
 * 权限规则model
 */
class AuthGroupModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'auth_group';

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
			'group_id'=>$map['id']
		];
		// 删除关联表中的组数据
        $AuthGroupAccessModel = new AuthGroupAccessModel();
		$result = $AuthGroupAccessModel->deleteData($group_map);
		return $result;
	}



}
