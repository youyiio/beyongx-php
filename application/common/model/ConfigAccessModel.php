<?php
namespace app\common\model;

use think\Model;

class ConfigAccessModel extends Model
{
    protected $name = CMS_PREFIX . 'config_access';

    protected $type = [
        'create_time' => 'datetime'
    ];

    protected $pk = 'access_id';
}

?>