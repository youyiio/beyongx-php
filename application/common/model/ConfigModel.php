<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-09-05
 * Time: 15:22
 */

namespace app\common\model;


use think\Model;

class ConfigModel extends Model
{
    protected $name = CMS_PREFIX . 'config';
    protected $pk = 'name';
}