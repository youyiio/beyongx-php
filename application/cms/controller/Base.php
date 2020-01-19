<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-03-16
 * Time: 12:31
 */

namespace app\cms\controller;

use think\Controller;

class Base extends Controller
{

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return $this->fetch('public/404');
    }

    public function initialize()
    {
        parent::initialize();
        if (!session('uid') && !session('visitor')) {
            $ip = request()->ip(0, true);
            $visitor = '游客-' . ip_to_address($ip, 'province,city');
            session('visitor', $visitor);
        }
    }

}