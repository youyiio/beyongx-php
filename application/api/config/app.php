<?php

return [
    'app_trace'              => false,

    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '\\app\\common\\exception\\ApiHandle',
    'default_return_type'   => 'json', // 默认输出类型

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => 'public/success', //Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => 'public/error', //Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    //'exception_tmpl'         => 'public/500.html', //Env::get('think_path') . 'tpl/think_exception.tpl',

    'appkey'                => '123456', // APP使用的密钥
    'user_group_id'         => 4, // 用户默认权限组 4 为普通用户
    'login_type'            => ['account', 'mobile', 'email'], // 用户登录方式
];
