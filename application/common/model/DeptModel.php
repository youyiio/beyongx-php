<?php 
namespace app\common\model;

class DeptModel extends BaseModel
{
    protected $name = 'sys_dept';
    protected $pk = 'id';
    

    const STATUS_ACTIVED = 1;
}