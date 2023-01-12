<?php
/**
 * frontend模块路由，针对seo优化进行设置
 * User: cattong
 * Date: 2019-02-18
 * Time: 11:35
 */

return [

    /*****************frontend 通用路由 begin*******************/
    //首页
    'index' => ['frontend/Index/index', ['method'=>'get']],
    'business' => ['frontend/Index/business', ['method'=>'get']],
    'team' => ['frontend/Index/team', ['method'=>'get']],
    'partner' => ['frontend/Index/partner', ['method'=>'get']],
    'about' => ['frontend/Index/about', ['method'=>'get']],
    'contact' => ['frontend/Index/contact', ['method'=>'get']],
    'index/:name' => ['frontend/Index/__extPage', ['method'=>'get']], //可动态扩充页面

    //用户操作
    'sign/index' => ['frontend/Sign/index', ['method'=>'get,post']],
    'sign/login' => ['frontend/Sign/login', ['method'=>'get,post']],
    'sign/register' => ['frontend/Sign/register', ['method'=>'get,post']],
    'sign/logout' => ['frontend/Sign/logout', ['method'=>'get,post']],
    'sign/forget' => ['frontend/Sign/forget', ['method'=>'get,post']],
    'sign/captcha' => ['frontend/Sign/captcha', ['method'=>'get,post']],
    'sign/sendCode' => ['frontend/Sign/sendCode', ['method'=>'get,post']],
    
    /*****************frontend 通用路由 end*******************/
];