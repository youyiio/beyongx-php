<?php

// JWT配置
return [
    // JWT配置
    'jwt_alg'             => 'HS256',
    'jwt_key'             => 'xp1yutwt8Fxj5XEzes4j-X4tCBQwE0',
    'jwt_expired_time'    => 3600, //过期时间秒
    'jwt_auth_on'         => 'on', //api权限验证, on|off
    'jwt_action_excludes' => [
        '/sign/login', //登录
        '/sign/register',  //注册
        '/sms/sendcode', //发送验证码
        '/sms/login', //短信登录
    ],  //jwt验证例外列表
];