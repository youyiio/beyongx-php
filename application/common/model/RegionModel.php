<?php
namespace app\common\model;

use think\Model;

class RegionModel extends Model
{
    protected $name = 'sys_region';
    protected $pk = 'id';

    //定义常量
    const LEVEL_PROVINCE = 1; //省级
    const LEVEL_CITY = 2;  //市级
    const LEVEL_AREA = 3; //县(区)级

    //关联表：自关联,所属地区
    public function parentRegion()
    {
        return $this->belongsTo('RegionModel','pid','id');
    }

    //关联表：自关联,管辖地区
    public function childRegions()
    {
        return $this->hasMany('RegionModel','id','pid');
    }

}