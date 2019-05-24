<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 14:55
 */

namespace app\common\model;


use think\Model;

class FileModel extends Model
{
    protected $name = CMS_PREFIX . 'file';

    protected $pk = 'file_id';
}