<?php
namespace app\common\model\api;

use app\common\model\BaseModel;

class ConfigAccessModel extends BaseModel
{
    protected $name = 'api_config_access';
    protected $pk = 'access_id';

    //自动完成
    protected $auto = [''];
    protected $insert = ['create_time'];
    protected $update = [];
}