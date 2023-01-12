<?php
namespace app\admin\controller;

use app\common\model\cms\AdServingModel;
use app\common\model\cms\AdModel;
use app\common\model\cms\AdSlotModel;

/**
* 广告控制器
*/
class Ad extends Base
{
    //广告内链列表
    public function index()
    {
        $title = input('param.title', '');
        $slotId = input('param.slot_id', '');

        $where = [];
        if (!empty($title)) {
            $where[] = ['title', 'like', "%$title%"];
        }
        if (!empty($slotId)) {
            $AdServingModel = new AdServingModel();
            $adIds = $AdServingModel->where('slot_id', $slotId)->field('distinct ad_id')->column('ad_id');//column变成一维数组
            $where[] = ['id', 'in', $adIds];
        }

        $AdModel = new AdModel();
        $list = $AdModel->where($where)->order('sort asc,id desc')->paginate(10, false, ['query'=>input('param.')]);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        //广告槽列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id, title')->select();
        $this->assign('slotList', $slotList);

        return $this->fetch('ad/index');
    }

    //新增广告内链
    public function addAd()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $rule = [
                'title|标题' => 'require',
                'url' => ['require'],
                'slot_ids' => ['require'],
                //'image_id|专题图片' => 'require|number',
            ];
            $check = $this->validate($data, $rule);
            if ($check !== true) {
                $this->error($check);
            }

            $AdModel = new AdModel();
            $data['create_time'] = date_time();
            $rowsNum = $AdModel->isUpdate(false)->allowField(true)->save($data);

            //新增中间表数据
            $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
            $AdModel->adSlots()->attach($data['slot_ids'], $pivot);

            if ($rowsNum !== false) {
                $this->success('成功新增广告', url('ad/index'));
            } else {
                $this->error('新增失败');
            }
        }

        //类型列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id,title,name,remark')->select();
        $this->assign('slotList', $slotList);

        return $this->fetch('ad/addAd');
    }

    //修改广告内链
    public function editAd($adId = 0)
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $rule = [
                'id' => ['require'],
                'title|标题' => 'require',
                'url' => ['require'],
                'slot_ids' => ['require'],
                //'image_id|专题图片' => 'require|number',
            ];
            $check = $this->validate($data,$rule);
            if ($check !== true) {
                $this->error($check);
            }

            $data['create_time'] = date_time();
            $id = $data['id'];
            $AdModel = new AdModel();
            $rowsNum = $AdModel->isUpdate(true)->allowField(true)->save($data, ['id'=>$id]);

            //更新中间表数据
            $AdModel->adSlots()->detach();
            $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
            $AdModel->adSlots()->attach($data['slot_ids'], $pivot);

            if ($rowsNum !== false) {
                $this->success('成功修改广告', url('ad/index'));
            } else {
                $this->error('修改失败');
            }
        }

        $ad = AdModel::get(['id' => $adId]);
        if (empty($ad)) {
            $this->error('广告不存在');
        }
        $this->assign('ad', $ad);

        //old slots
        $relationSlots = $ad->adSlots;
        $oldSlots = [];
        foreach ($relationSlots as $adSlot) {
            $oldSlots[] = $adSlot['id'];
        }
        $this->assign('oldSlots', $oldSlots);

        //类型列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id,title,name')->select();
        $this->assign('slotList', $slotList);

        return $this->fetch('ad/addAd');
    }

    //删除广告内链
    public function deleteAd($adId = 0)
    {
        $res = AdModel::destroy($adId);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //广告排序
    public function orderAd()
    {
        $data = input('post.');
        $AdModel = new AdModel();
        foreach ($data as $k => $v) {
            $AdModel->where('id', $k)->setField('sort', $v);
        }
        $this->success('成功排序');
    }




}
