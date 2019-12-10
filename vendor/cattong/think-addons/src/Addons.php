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
namespace think;

use think\View;
use think\Db;

/**
 * 插件基类
 * Class Addons
 * @author Beyongx
 * @package think\addons
 */
abstract class Addons
{
    const STATUS_DELETED = -1; //已删除
    const STATUS_UNKNOWN = 0;  //未知状态
    const STATUS_DOWNLOADING = 1;  //下载中
    const STATUS_DOWNLOADED = 2; //已下载
    const STATE_INSTALLING = 3; //安装中
    const STATE_INSTALLED = 4; //已安装
    const STATE_UNINSTALLING = 5; //卸载中
    const STATE_UNINSTALLIED = 6; //已卸载

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $view = null;

    // 当前错误信息
    protected $error;

    /**
     * $config = [
     *  'name'          => 'Test',
     *  'title'         => '测试插件',
     *  'description'   => '用于thinkphp5的插件扩展演示',
     *  'status'        => 1,
     *  'author'        => 'byron sampson',
     *  'version'       => '0.1'
     * ]
     */
    public $config = [];

    public $addons_path = '';
    public $config_file = '';

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        // 获取当前插件目录
        $this->addons_path = \think\facade\Env::get('addons_path') . $this->getName() . DIRECTORY_SEPARATOR;
        // 读取当前插件配置信息
        if (is_file($this->addons_path . 'config.php')) {
            $this->config_file = $this->addons_path . 'config.php';
        }

        // 初始化视图模型
        $config = ['view_path' => $this->addons_path];
        $config = array_merge(\think\facade\Config::get('template.'), $config);
        //$this->view = new View($config, \think\facade\Config::get('view_replace_str'));
        $this->view = Container::get('view')->init($config);

        // 控制器初始化
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array|mixed|null
     */
    final public function getConfig($name = '')
    {
        static $_config = array();
        if (empty($name)) {
            $name = $this->getName();
        }
        if (isset($_config[$name])) {
            return $_config[$name];
        }
        $map['name'] = $name;
        $map['status'] = 1;
        $config = [];
        if (is_file($this->config_file)) {
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if ($value['type'] == 'group') {
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                } else {
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
            unset($temp_arr);
        }
        $_config[$name] = $config;

        return $config;
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 检查配置信息是否完整
     * @return bool
     */
    final public function checkConfig()
    {
        $configCheckKeys = ['name', 'title', 'description', 'status', 'author', 'version'];
        foreach ($configCheckKeys as $value) {
            if (!array_key_exists($value, $this->config)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @throws \Exception
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if (!is_file($template)) {
            $template = '/' . $template;
        }
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     */
    public function display($content, $vars = [], $replace = [], $config = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @return mixed
     */
    public function show($content, $vars = [])
    {
        // 关闭模板布局
        $this->view->engine->layout(false);

        echo $this->view->fetch($content, $vars, [], [], true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    public function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 获取当前错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    //钩子方法不存在，执行插件类:run方法
    public function run($param)
    {
        echo 'execute run method, maybe hook method not exists.';
        //cache('addons', null);
        //cache('hooks', null);
    }

    /**
     * 判断是否插件已经安装
     * @return bool
     */
    public function getStatus()
    {
        $name = $this->config['name'];
        $where = [
            ['name', '=', $name]
        ];

        $addon = db('addons')->where($where)->find();
        if ($addon) {
            return $addon['status'];
        }

        return self::STATUS_UNKNOWN;
    }

    /**
     * 必须实现，插件初始安装时，插件系统会调用此方法
     * @return bool
     */
    abstract public function install();

    /**
     * 必须实现，插件卸载时，插件系统会调用此方法
     * @return bool
     */
    abstract public function uninstall();

}
