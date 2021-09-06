<?php

/**
 * 模块控制器Sign的登录、注销、注册、忘记密码的配置信息
 */
return [
    'login_multi_client_support' => false, //支持单个用户多个端同时登录
    'login_success_view' => 'cms/Member/overview', //登录成功后，跳转地址
    'logout_success_view' => 'frontend/Index/index', //注销后，跳转地址
    'register_enable' => false, //注册功能是否支持
    'register_code_type' => 'mail', //注册码方式，值为：mail,mobile
    'reset_enable' => false, //忘记密码功能是否支持
    'reset_code_type' => 'mail', //重置密码，值为：mail,mobile
];
