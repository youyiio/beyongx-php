<?php 
namespace app\common\model;

class JobModel extends BaseModel
{
    protected $name = 'sys_job';
    protected $pk = 'id';

    const STATUS_OFFLINE  = 0;  //下线
    const STATUS_ONLINE   = 1;  //上线


    //关联用户表
    public function users()
    {
        return $this->belongsToMany('userModel', config('database.prefix') . 'sys_user_role', 'job_id', 'uid');
    }

    //关联中间表 roleMenuModel
    public function userJob()
    {
        return $this->hasMany('userJobModel', 'job_id', 'id');
    }

    
}