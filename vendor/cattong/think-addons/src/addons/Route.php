<?php
// +----------------------------------------------------------------------
// | thinkphp5 Addons [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@qq.com>
// +----------------------------------------------------------------------
namespace think\addons;

use think\facade\Hook;

/**
 * 插件执行默认控制器
 * Class AddonsController
 * @package think\addons
 */
class Route extends \think\Controller
{
    /**
     * 插件执行
     */
    public function execute()
    {
        // 处理路由参数
        $addon = $this->request->param('addon', '');
        $controller = $this->request->param('controller', '');
        $action = $this->request->param('action', '');
        // 是否自动转换控制器和操作名
        $convert = \think\facade\Config::get('url_convert');
        // 格式化路由的插件位置
        $action = $convert ? strtolower($action) : $action;
        $controller = $convert ? strtolower($controller) : $controller;
        $addon = $convert ? strtolower($addon) : $addon;

        if (!empty($addon) && !empty($controller) && !empty($action)) {
            // 获取类的命名空间
            $class = get_addons_class($addon, 'controller', $controller);
            if (class_exists($class)) {
                $model = new $class();
                if ($model === false) {
                    abort(500, lang('addon init fail'));
                }
                // 调用操作
                if (!method_exists($model, $action)) {
                    abort(500, lang('Controller Class Method Not Exists'));
                }
                // 监听addons_init
                Hook::listen('addons_init', $this);
                return call_user_func_array([$model, $action], [$this->request]);
            } else {
                abort(500, lang('Controller Class Not Exists'));
            }
        }
        abort(500, lang('addon cannot name or action'));
    }
}
