<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;


class AdSlotModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'ad_slot';

    const SLOT_BANNER_HEADLINE = "banner_headline"; //首页轮播图carousel

    protected $pk = 'id';
}