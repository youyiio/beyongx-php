<?php

namespace app\common\model;

use think\Model;

/**
 * 权限规则model
 */
class UserJobModel extends BaseModel
{
    protected $name = 'sys_user_job';

    /**
     * 根据group_id获取全部用户id
     * @param  int $group_id 用户组id
     * @return array         用户数组
     */
    public function getUidsByJobId($job_id)
    {
        $uids = $this->where(['job_id' => $job_id])->column('uid');
        return $uids;
    }
}
