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

use think\App;
use think\facade\Hook;
use think\facade\Config;
use think\Loader;
use think\facade\Cache;
use think\facade\Route;
use think\facade\Env;

// 插件目录,app_init时，Env:get('root_path')为空
//define('ADDON_PATH', \think\facade\Env::get('root_path') . 'addons' . DIRECTORY_SEPARATOR);

// 定义路由
Route::get('addons/[:addon]/[:controller]/[:action]', "\\think\\addons\\Route@execute");

// 闭包自动识别插件目录配置
Hook::add('app_init', function () {

    Env::set('addons_path', Env::get('root_path') . 'addons' . DIRECTORY_SEPARATOR);

    // 注册类的根命名空间
    Loader::addNamespace('addons', Env::get('addons_path'));
    // 如果插件目录不存在则创建
    if (!is_dir(Env::get('addons_path'))) {
        @mkdir(Env::get('addons_path'), 0777, true);
    }

    // 获取开关
    $autoload = (bool)Config::get('addons.autoload', false);
    // 非正是返回
    if (!$autoload) {
        return;
    }
    // 当debug时不缓存配置
    $config = \think\facade\Config::get('app_debug') ? [] : Cache::get('addons', []);
    if (empty($config)) {
        $config = (array)Config::get('addons.');

        // 方法1：从文件读取addons的配置************************
        if (strtolower($config['type']) == 'file') {
            // 读取插件目录及钩子列表
            $base = get_class_methods("\\think\\Addons");

            // 读取插件目录中的php文件
            foreach (glob(Env::get('addons_path') . '*/*.php') as $addons_file) {
                // 格式化路径信息
                $info = pathinfo($addons_file);
                // 获取插件目录名
                $name = pathinfo($info['dirname'], PATHINFO_FILENAME);
                // 找到插件入口文件
                if (strtolower($info['filename']) == strtolower($name)) {
                    // 读取出所有公共方法
                    $methods = (array)get_class_methods("\\addons\\" . $name . "\\" . $info['filename']);
                    // 跟插件基类方法做比对，得到差异结果
                    $hooks = array_diff($methods, $base);
                    // 循环将钩子方法写入配置中
                    foreach ($hooks as $hook) {
                        if (!isset($config['hooks'][$hook])) {
                            $config['hooks'][$hook] = [];
                        }
                        // 兼容手动配置项
                        if (is_string($config['hooks'][$hook])) {
                            $config['hooks'][$hook] = explode(',', $config['hooks'][$hook]);
                        }
                        if (!in_array($name, $config['hooks'][$hook])) {
                            $config['hooks'][$hook][] = $name;
                        }
                    }
                }
            }
        }
        //方法1 end *********************************
        //dump($config);

        //方法2：从数据库读取hooks配置*************************
        if (strtolower($config['type']) == 'db' && !empty(Config::get('database.database'))) {
            $HooksModel = new \app\common\model\HooksModel();
            $AddonsModel = new \app\common\model\AddonsModel();
            $hooks = $HooksModel->where('status', 1)->field('name,addons')->select();
            // 获取钩子的实现插件信息
            foreach ($hooks as $row) {
                $key = $row['name'];
                $value = $row['addons'];
                if ($value) {
                    $map = [];
                    $map[] = ['status', '=', 1];
                    $names = explode(',', $value);
                    $map[] = ['name', 'IN', $names];
                    $data = $AddonsModel->where($map)->column('name');
                    if ($data) {
                        $addons = array_intersect($names, $data);
                        $config['hooks'][$key] = $addons;
                    }
                }
            }
        }
        //方法2 end *********************************
        //dump($config);

        Cache::set('addons', $config);
    }

//    Config::set('addons', $config);
});

// 闭包初始化行为
Hook::add('action_begin', function () {
    // 获取系统配置
    $data = \think\facade\Config::get('app_debug') ? [] : Cache::get('hooks', []);
    $addons = (array)Cache::get('addons');
    $addons = $addons['hooks'];
    if (empty($data)) {
        // 初始化钩子
        foreach ($addons as $key => $values) {
            if (is_string($values)) {
                $values = explode(',', $values);
            } else {
                $values = (array)$values;
            }
            $addons[$key] = array_filter(array_map('get_addons_class', $values));//获取插件类
            Hook::add($key, $addons[$key]);//key为钩子名称，值为具体的插件类,如\addons\test\Test,默认优先执行插件类的:key方法，如果方法不存在，执行插件类:run方法
        }
        Cache::set('hooks', $addons);
    } else {
        Hook::import($data, false);
    }
});

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = [])
{
    $data = Cache::get('hooks', []);
    if (!isset($data[$hook])) {
        echo '<script>console.warn("hook:' . $hook . ' not exist");</script>';
        return;
    }

    Hook::listen($hook, $params);
}

/**
 * 获取插件类的类名
 * @param $name 插件名
 * @param string $type 返回命名空间类型
 * @param string $class 当前类名
 * @return string
 */
function get_addons_class($name, $type = 'hook', $class = null)
{
    $name = Loader::parseName($name);
    // 处理多级控制器情况
    if (!is_null($class) && strpos($class, '.')) {
        $class = explode('.', $class);
        foreach ($class as $key => $cls) {
            $class[$key] = Loader::parseName($cls, 1);
        }
        $class = implode('\\', $class);
    } else {
        $class = Loader::parseName(is_null($class) ? $name : $class, 1);
    }
    switch ($type) {
        case 'hook':
            $namespace = "\\addons\\" . $name . "\\" . $class;
            break;
        case 'controller':
            $namespace = "\\addons\\" . $name . "\\controller\\" . $class;
            break;
        default:
            $namespace = "\\addons\\" . $name . "\\" . $class;;
    }

    return class_exists($namespace) ? $namespace : '';
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 * @return array
 */
function get_addons_config($name)
{
    $class = get_addons_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return [];
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param $url
 * @param array $param
 * @return bool|string
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 */
function addons_url($url, $param = [], $suffix = true, $domain = false)
{
    $url = parse_url($url);
    $case = config('url_convert');
    $addons = $case ? Loader::parseName($url['scheme']) : $url['scheme'];
    $controller = $case ? Loader::parseName($url['host']) : $url['host'];
    $action = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    // 生成插件链接新规则
    $actions = "{$addons}-{$controller}-{$action}";

    return url("addons/execute/{$actions}", $param, $suffix, $domain);
}