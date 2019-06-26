<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-03-19
 * Time: 18:10
 */

namespace app\common\model;


use think\Model;

class CrawlerMetaModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'crawler_meta';
    protected $pk = 'id';

    const STATUS_WAREHOUSING = 'warehousing'; //已入库
    const STATUS_PENDING = 'pending'; //执行中
    const STATUS_FAIL = 'fail'; //失败或未有内容
    const STATUS_COMPLETE = 'complete'; //完成

    protected $auto = ['update_time'];
    protected $insert = ['create_time', 'update_time'];
    protected $update = ['update_time'];


}