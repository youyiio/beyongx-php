<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;

/**
*   广告模型
*/
class AdModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'ad';

    protected $pk = 'id';
    //自动完成
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = [];

    protected static function init()
    {
        AdModel::afterInsert(function ($ad) {
            AdModel::clearCache($ad);
        });
        AdModel::afterUpdate(function ($ad) {
            AdModel::clearCache($ad);
        });
        AdModel::afterDelete(function ($ad) {
            AdModel::clearCache($ad);
        });
    }

    //关联表:中间表
    public function adSlots()
    {
        return $this->belongsToMany('AdSlotModel', config('database.prefix'). CMS_PREFIX . 'ad_serving', 'slot_id', 'ad_id');
    }

    //关联表:图片
    public function image()
    {
        return $this->hasOne('app\common\model\ImageModel','id','image_id');
    }

    //关联表:投放时间段
    public function adServings()
    {
        return $this->hasMany('AdServingModel', 'ad_id','id');
    }

    //清理缓存
    public static function clearCache($ad)
    {
        $ad = $ad->toArray();
        if (isset($ad['slot_id'])) {
            if ($ad['slot_id'] == 1) {
                cache('headline',null);
            }
            if ($ad['slot_id'] == 3) {
                cache('banner',null);
            }
        }
    }
}
