<?php 
namespace app\common\model;

class JobModel extends BaseModel
{
    protected $name = 'sys_job';
    protected $pk = 'id';

    const STATUS_OFFLINE  = 0;  //下线
    const STATUS_ONLINE   = 1;  //上线
    
}