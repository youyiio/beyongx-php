<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [

    //队列需公共配置的参数
    'connector' => 'Sync',  //驱动类型，可选择 Sync(默认):同步执行；Database:数据库驱动；Redis:Redis驱动；Topthink:Topthink驱动，ThinkPHP内部的队列通知服务平台
    'expire'    => 60,            // 任务的过期时间，默认为60秒; 若要禁用，则设置为 null
    'default'   => 'cms:queue',    // 默认的队列名称，上线建设配置成 cms:queue:project

    //Redis驱动，需配置的参数
    'host'       => '127.0.0.1',	// redis 主机ip
    'port'       => 6379,		// redis 端口
    'password'   => '',		// redis 密码
    'select'     => 0,		// 使用哪一个 db，默认为 db0
    'timeout'    => 0,		// redis连接的超时时间
    'persistent' => false,		// 是否是长连接

    //Database驱动，需配置的参数
//    'table'     => 'jobs',       // 存储消息的表名，不带前缀
//    'dsn'       => [],

    //Topthink驱动，需配置的参数
//    'token'       => '',
//    'project_id'  => '',
//    'protocol'    => 'https',
//    'host'        => 'qns.topthink.com',
//    'port'        => 443,
//    'api_version' => 1,
//    'max_retries' => 3,

    //Sync驱动，需配置的参数；
    //无需其他配置参数；该驱动的实际作用是取消消息队列，还原为同步执行
];
