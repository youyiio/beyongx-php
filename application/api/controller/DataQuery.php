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
    public function areas()
    {
        $pid = input('pid/d', '0');
        $drilldown = input('drilldown/d', 0);

        $RegionModel = new RegionModel();
        if ($drilldown > 1) {
            $list = cache('area_pid_' . $pid . 'drilldown_' . $drilldown);
            if ($list) {
                return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
            }

            if ($drilldown == 2) {
                $list = $RegionModel->where('pid', '=', $pid)->select();
                foreach ($list as $key => $value) {
                    $list[$key]['children'] = $RegionModel->where('pid', '=', $value['id'])->select();
                }
            } elseif ($drilldown == 3) {
                $list = $RegionModel->select();
                $list = $this->getTree($list, $pid, 'id', 'pid', $drilldown);
            }
            cache('area_pid_' . $pid . 'drilldown_' . $drilldown, $list);
        } else {
            $where[] = ['pid', '=', $pid];
            $list  = $RegionModel->where($where)->select();
        }

        $list = parse_fields($list, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
    }

    //获取树状结构
    protected function getTree($data, $pid = 0, $fieldPK = 'id', $fieldPid = 'pid', $depth = 1, $currentDepth = 1)
    {
        if (empty($data)) {
            return array();
        }

        $arr = array();
        foreach ($data as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr[$v[$fieldPK]] = $v;
                $arr[$v[$fieldPK]]['level'] = $currentDepth;
                $children = $this->getTree($data, $v[$fieldPK], $fieldPK, $fieldPid, $depth, $currentDepth + 1);

                if (empty($children)) {
                    continue;
                }
                //判断深度
                if ($depth == $currentDepth) {
                    continue;
                }
                $arr[$v[$fieldPK]]['children'] = $children;
            }
        }

        return array_merge($arr);
    }
}
