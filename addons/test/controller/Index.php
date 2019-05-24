<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-04-23
 * Time: 16:35
 */
namespace addons\test\controller;

use think\addons\Controller;

class Index extends Controller
{
    use \app\common\controller\AdminBase; //使用trait

    public function index()
    {
        return $this->display('<div><h2>插件控件器！！<br/>Index extends \think\addons\Controller; <br/>Duang Duang Duang</h2></div>');
    }

    public function link()
    {
        $this->check(); //管理员权限检测
        return $this->fetch('link');
    }
}