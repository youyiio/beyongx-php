<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2019-06-03
 * Time: 14:21
 */

namespace app\common\model;


abstract class BaseMetaModel extends BaseModel
{

    //读取|设置meta值
    abstract function _meta($fkId, $metaKey='', $metaValue='', $mode=BaseModel::MODE_SINGLE_VALUE);

    //读取metas多值
    abstract function _metas($fkId, $metaKey='');
}