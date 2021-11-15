<?php
namespace app\api\controller\app;

use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\model\ConfigModel;

class Config extends Base
{
    //查询应用信息
    public function base()
    {
        $ConfigModel = new ConfigModel();
        
        $fields = 'id,name,group,key,value,value_type,remark';
        $list = $ConfigModel->field($fields)->select();

        $returnData = parse_fields($list, 1);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //查询状态字典
    public function status($name)
    {
        $ConfigModel = new ConfigModel();
        $list = $ConfigModel->where('group', '=', $name.'_status')->field('key,value')->select();

        if (empty($list)) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '数据未找到');
        }

        $returnData = [];
        foreach ($list as $val) {
            $returnData[] = [
                $val['key'] => $val['value']
            ];
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }
}