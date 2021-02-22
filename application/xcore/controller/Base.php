<?php
namespace app\xcore\controller;

use think\Controller;

/**
 * xcore Controller基类
 * @package app\xcore\controller
 */
class Base extends Controller
{
    use \app\common\controller\AdminBase; //使用trait

    protected $uid;

    public function initialize()
    {
        $this->check();
    }

}