<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;
use think\Model;

class AdServingModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'ad_serving';

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = ['update_time'];
}