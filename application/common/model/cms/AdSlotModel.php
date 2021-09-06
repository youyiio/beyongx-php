<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;


class AdSlotModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'ad_slot';

    const TYPE_BANNER_HEADLINE = 1;//首页头条
    const TYPE_BANNER_CENTER = 2; //首页中间广告

    protected $pk = 'id';
}