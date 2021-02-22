<?php
namespace app\xcore\controller;

use EasyWeChat\Factory;
use think\Controller;

class Wechat extends Controller
{
    public function index()
    {
        $config = config("wechat");
        $app = Factory::officialAccount($config);

    }
}