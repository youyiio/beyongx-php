<?php

// JWT配置
return [
    // JWT配置
    'jwt_alg'             => 'HS256',
    'jwt_key'             => 'xp1yutwt8Fxj5XEzes4j-X4tCBQwE0',
    'jwt_expired_time'    => 3600, //过期时间秒
    'jwt_action_excludes' => [
        '/sign/login', 
        '/sign/register', 
        '/sms/sendcode', '/sms/login',
        '/anime/categorylist', '/anime/topiclist', 
        '/anime/chapterlist', '/anime/getanime'
    ],  //jwt验证例外列表
];