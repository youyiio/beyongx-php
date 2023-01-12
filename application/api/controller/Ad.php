<?php
namespace app\api\controller;

// 不需要认证的话继承Base
use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\model\cms\AdModel;
use app\common\model\cms\AdServingModel;
use app\common\model\cms\AdSlotModel;
use app\common\model\ImageModel;
use think\Validate;

class Ad extends Base
{
    //查询轮播图
    public function carousel() 
    {
        $params = $this->request->put();

        //数据验证git
        $validate = new Validate();
        $validate->rule([
            'slot' => 'require',
            'limit' => 'require|integer'
        ]);
        if (!$validate->check($params)) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, $validate->getError());
        };

        $slot = $params["slot"];
        $limit = $params["limit"];

        $AdSlotModel = new AdSlotModel();
        $adSlot = $AdSlotModel->where(['name' => $slot])->find();
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


        $page = $params['page']?? 1;
        $size = $params['size']?? 10;
        $filters = $params["filters"];
        $keyword = $filters['keyword']?? '';
        $soltIds = $filters['soltIds']?? '';

        $where = [];
        if (!empty($keyword)) {
            $where[] = ['title', 'like', "%$keyword%"];
        }
        if (!empty($soltIds)) {
            $AdServingModel = new AdServingModel();
            $adIds = $AdServingModel->where('slot_id', $soltIds)->field('distinct ad_id')->column('ad_id');//column变成一维数组
            $where[] = ['id', 'in', $adIds];
        }

        $AdModel = new AdModel();
        $list = $AdModel->where($where)->order('sort asc,id desc')->paginate($size, false, ['page'=>$page]);

        foreach ($list as $ad) {
            $ad['image'] = $this->findImage($ad);
        }

        $list = $list->toArray();
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //查询广告插槽
    public function slots()
    {
        $adSlot = new AdSlotModel();
        $list = $adSlot->select();
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);

    }

    //新增广告
    public function create()
    {
        $params = $this->request->put();

        //数据验证
        $validate = new Validate();
        $validate->rule([
            'title' => 'require',
            'url' => ['require','url'],
            'slotIds' => ['require','array'],
            'imageId' => ['require','integer']
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '操作失败', $validate->getError());
        }

        $AdModel = new AdModel();
        $params['create_time'] = date_time();
        $soltIds = $params['slotIds'];
        //驼峰转为下划线
        $params = parse_fields($params);
        $AdModel->isUpdate(false)->allowField(true)->save($params);

        $adId = $AdModel->id;
        if (!$adId) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        }

        //新增中间表数据
        $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
        $AdModel->adSlots()->attach($soltIds, $pivot);

        //返回数据
        $ad = AdModel::get($adId);

        $returnData = $ad;
        $returnData['image'] = $this->findImage($ad);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑广告
    public function edit()
    {
        $params = $this->request->put();

        //数据验证
        $validate = new Validate();
        $validate->rule([
            'id' => 'require',
            'title' => 'require',
            'url' => 'require',
            'slotIds' => ['require','array'],
            'imageId' => ['require','integer']
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_VALIDATE_ERROR, '操作失败!', $validate->getError());
        }

        $params['create_time'] = date_time();
        $id = $params['id'];
        $AdModel = new AdModel();
        $rowsNum = $AdModel->isUpdate(true)->allowField(true)->save($params, ['id'=>$id]);

        //更新中间表数据
        $AdModel->adSlots()->detach();
        $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
        $AdModel->adSlots()->attach($params['slotIds'], $pivot);

        if ($rowsNum == false) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        } 

        //返回数据
        $ad = AdModel::get($id);
        $returnData = $ad;
        $returnData['image'] = $this->findImage($ad);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    public function delete($id)
    {
        $ad = AdModel::get($id);
        if (!$ad) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '广告不存在!');
        }

        $res = $ad->delete();
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $AdServingModel = new AdServingModel();
        $AdServingModel->where('ad_id', $id)->delete();
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查找广告图
    public function findImage($ad)
    {
        $Image = [];
        if (empty($ad['image_id']) || $ad['image_id'] == 0) {
            return $Image;
        }
        $ImageModel = new ImageModel();
        $Image = $ImageModel::get($ad['image_id']);
    
        if (empty($Image)) {
            return $Image;
        }

        //完整路径
        $Image['fullImageUrl'] = $ImageModel->getFullImageUrlAttr('',$Image);
        $Image['FullThumbImageUrlAttr'] = $ImageModel->getFullThumbImageUrlAttr('',$Image);
        unset($Image['remark']);
        unset($Image['image_size']);
        unset($Image['thumb_image_size']);
        unset($ad['image_id']);

        $Image = $Image->toArray();
        $Image = parse_fields($Image,1);
        return $Image;
    }
}