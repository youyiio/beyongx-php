<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-03-05
 * Time: 11:22
 */

namespace app\common\logic;


use app\common\model\AdModel;
use think\Model;

class AdLogic extends Model
{
    //获取广告或内链
    public function getAdList($type=0, $limit=5)
    {
        $where = [];
        $AdModel = new AdModel();
        if ($type) {
            $AdModel = AdModel::has('adAdtype', ['type'=>$type]);
        }

        $list = $AdModel->where($where)->order('sort asc')->limit($limit)->select();

        return $list;
    }
}