<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-03-16
 * Time: 12:20
 */

namespace app\admin\controller;

/**
 * 空控制器,空操作
 * 'empty_controller'       => 'Error',
 */
class Error
{
    public function _empty()
    {
        return view('public/404');
    }
}