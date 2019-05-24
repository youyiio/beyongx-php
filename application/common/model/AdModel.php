<?php
namespace app\common\model;

use think\Model;

/**
*   广告模型
*/
class AdModel extends BaseModel
{
    protected $name = CMS_PREFIX. 'ad';

    const TYPE_HEADLINE = 1; //首页滚动图
    const TYPE_BANNER = 2;   //banner广告图

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

    //关联表:类型
    public function adtypes()
    {
        return $this->belongsToMany('AdtypeModel', config('database.prefix'). CMS_PREFIX . 'ad_adtype', 'type', 'ad_id');
    }

    //关联表:图片
    public function image()
    {
        return $this->hasOne('ImageModel','image_id','image_id');
    }

    //关联表:文章
//    public function articles()
//    {
//        return $this->belongsToMany('Article', 'article_id', 'ad_id');
//    }

    //关联表:中间表
    public function adAdtype()
    {
        return $this->hasMany('AdAdtypeModel', 'ad_id','id');
    }

    //清理缓存
    public static function clearCache($ad)
    {
        $ad = $ad->toArray();
        if (isset($ad['type'])) {
            if ($ad['type'] == 1) {
                cache('headline',null);
            }
            if ($ad['type'] == 3) {
                cache('banner',null);
            }
        }
    }
}
