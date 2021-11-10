<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\JobModel;

class Job extends Base
{
    //查询部门字典
    public function dict()
    {
        
        $JobModel = new JobModel();
        $list = $JobModel->field('id,name,remark')->select();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
    }
}