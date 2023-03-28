<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2017-08-17
 * Time: 16:18
 */

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\RegionModel;
use think\Log;

class DataQuery extends Base
{
    /**
     * @param string $format, 返回的格式，方便前台显示，支持:raw(原生不转化), weui,mui
     * @return \think\response\Json
     */
    public function areas()
    {
        $pid = input('pid/s', '0');
        // $drilldown = input('drilldown/s', '');

        $RegionModel = new RegionModel();
        $where[] = ['pid', '=', $pid];
        $list  = $RegionModel->where($where)->select();
      
        $list = parse_fields($list, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);

    }

}