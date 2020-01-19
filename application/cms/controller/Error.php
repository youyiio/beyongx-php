<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-03-16
 * Time: 12:31
 */

namespace app\cms\controller;

/**
 * 空控制器,空操作
 * 'empty_controller'       => 'Error',
 */
class Error
{
    public function _empty()
    {
        return $this->fetch('public/404');
    }
}