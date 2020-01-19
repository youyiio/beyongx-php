<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-03-05
 * Time: 11:31
 */

namespace app\common\model;


use think\Model;

class AdServingModel extends Model
{
    protected $name = CMS_PREFIX . 'ad_serving';

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = ['update_time'];
}