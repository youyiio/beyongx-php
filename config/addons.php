<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-04-23
 * Time: 16:29
 */

return [
    // 是否自动读取取插件钩子配置信息（默认是关闭）
    'autoload' => true,
    //启用配置管理的方式：数据库: db, 配置文件: file, 自动扫描: auto
    'type' => 'db',
    //type为file时，需配置以下信息，---当关闭自动获取配置时需要手动配置hooks信息
    'hooks' => [
        // 可以定义多个钩子, 一个钩子对应多个插件时可以用数组也可以用逗号分割
        //'testhook' => 'test' // 键为钩子名称，用于在业务中自定义钩子处理，值为实现该钩子的插件，
        //....
    ]
];