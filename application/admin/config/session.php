<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

use think\facade\Env;

$global_config = require(Env::get('root_path') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php');
return array_merge($global_config, [
    // SESSION 前缀
    'prefix'         => 'admin_',
    // session有效期 0表示永久缓存,3600表示60*60一个小时
    'expire' => 3600,
]);
