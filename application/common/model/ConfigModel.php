<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-09-05
 * Time: 15:22
 */

namespace app\common\model;


use think\Model;

class ConfigModel extends Model
{
    protected $name = 'sys_config';
    protected $pk = 'id';

    const STATUS_ACTIVED = 1; //启用
    const STATUS_OFF = 0;  //停用

}