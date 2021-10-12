<?php
namespace app\api\controller;

// 不需要认证的话继承Base
use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\model\cms\AdModel;
use app\common\model\cms\AdSlotModel;
use think\Validate;

class Ad extends Base
{
    //查询轮播图
    public function carousel() 
    {
        $params = $this->request->put();

        //数据验证
        $validate = new Validate();
        $validate->rule([
            'slot' => 'require',
            'limit' => 'require|integer'
        ]);
        if (!$validate->check($params)) {
            return ajax_error(ResultCode::E_DATA_VERIFY_ERROR, $validate->getError());
        };

        $slot = $params["slot"];
        $limit = $params["limit"];

        $AdSlotModel = new AdSlotModel();
        $adSlot = $AdSlotModel->where(['title_en' => $slot])->find();
        if (!$adSlot) {
            return ajax_error(ResultCode::E_DATA_NOT_FOUND, "广告slot不存在!");
        }

        $slotId = $adSlot->id;
        $results = AdModel::has('adServings', ['slot_id' => $slotId])->order('sort asc')->limit($limit)->select();

        return ajax_success($results);
    }

    //查询广告列表
    public function list() 
    {
        $params = $this->request->put();

        //数据验证
        $validate = new Validate();
        $validate->rule([
            'slot' => 'require',
            'limit' => 'require|integer'
        ]);
        if (!$validate->check($params)) {
            return ajax_error(ResultCode::E_DATA_VERIFY_ERROR, $validate->getError());
        };

        $slot = $params["slot"];
        $limit = $params["limit"];

        $AdSlotModel = new AdSlotModel();
        $adSlot = $AdSlotModel->where(['title_en' => $slot])->find();
        if (!$adSlot) {
            return ajax_error(ResultCode::E_DATA_NOT_FOUND, "广告slot不存在!");
        }

        $slotId = $adSlot->id;
        $results = AdModel::has('adServings', ['slot_id' => $slotId])->order('sort asc')->limit($limit)->select();

        return ajax_success($results);
    }
}