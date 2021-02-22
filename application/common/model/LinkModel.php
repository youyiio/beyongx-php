<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-09-05
 * Time: 16:06
 */

namespace app\common\model;


use think\Model;

class LinkModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'link';
    protected $pk = 'id';

    const STATUS_OFFLINE  = 0;  //下线
    const STATUS_ONLINE   = 1;  //上线

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time', 'status' => self::STATUS_ONLINE];
    protected $update = [];
}