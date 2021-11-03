<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

//命名空间提前注册，项目部署到子目录时，避免 \app\common\thinkphp\App 报不存在
Loader::addNamespace('app', __DIR__ . '/../application/');

Container::getInstance()->bindTo('app', new \app\common\thinkphp\App(__DIR__ . '/../application/'));

// 支持事先使用静态方法设置Request对象和Config对象


// 执行应用并响应
Container::get('app')->run()->send();