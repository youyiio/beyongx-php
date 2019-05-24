<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-06-12
 * Time: 20:50
 */

namespace app\common\model;


class HooksModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'hooks';

    const STATUS_REMOVED = -1;
    const STATUS_INSTALLING = 0;
    const STATUS_INSTALLED = 1;
    const STATUS_UNINSTALL = 2;
}