<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-03-05
 * Time: 11:22
 */

namespace app\common\logic;


use app\common\model\cms\AdModel;
use think\Model;

class AdLogic extends Model
{
    //获取广告或内链
    public function getAdList($slotId=0, $limit=5)
    {
        $where = [];
        $AdModel = new AdModel();
        if ($slotId) {
            $AdModel = AdModel::has('adServings', ['slot_id'=>$slotId]);
        }

        $list = $AdModel->where($where)->order('sort asc')->limit($limit)->select();

        return $list;
    }
}