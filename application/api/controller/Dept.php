<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\DeptModel;

class Dept extends Base
{
    //查询部门字典
    public function dict()
    {
        $params = $this->request->put();

        $struct = $params['struct']?? '';

        $DeptModel = new DeptModel();
        $list = $DeptModel->field('id,pid,name')->select();

        if ($struct === 'list') {
            $data = getList($list);
        } else {
            $data = getTree($list);
        }

        $returnData = $data;

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}