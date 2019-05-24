<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-01-26
 * Time: 16:30
 */

namespace app\common\model;


class AdtypeModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'adtype';

    const TYPE_BANNER_HEADLINE = 1;//首页头条
    const TYPE_BANNER_CENTER = 2; //首页中间广告

    protected $pk = 'type';
}