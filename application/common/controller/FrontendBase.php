<?php
namespace app\common\controller;

use think\facade\Env;
use think\facade\View;
use think\facade\Config;

/**
 * Trait 前端页面 Base Controller 组件
 * 使用方法：use \app\common\controller\FrontendBase;
 * @package app\common\controller
 */
trait FrontendBase
{

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return $this->fetch('public/404');
    }

    public function initialize()
    {
        parent::initialize();
        if (!session('uid') && !session('visitor')) {
            $ip = request()->ip(0, true);
            $visitor = '游客-' . ip_to_address($ip, 'province,city');
            session('visitor', $visitor);
        }

        $this->themeConfig();
    }

    private function themeConfig()
    {
        //读取当前主题详细信息
        //$config = get_theme_config(request()->module());
        $config = get_theme_config('cms');

        /*根据配置和来访设备类型自动切换为电脑主题或手机主题。 start */
        $header = request()->header();
        $isWechat = isset($header['user-agent']) && preg_match('/micromessenger/', strtolower($header['user-agent']));
        if (request()->isMobile() || $isWechat) {
            $template = "mobile";
        } else {
            $template = "pc";
        }

        //设置所有主题的存放路径
        $themePath = Env::get('root_path')  . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $config['package_name'] . DIRECTORY_SEPARATOR;
        $viewPath = $themePath . 'tpl' . DIRECTORY_SEPARATOR;
        $paginateFile = $themePath . 'paginate.php';
        if (isset($config['responsive']) && $config['responsive'] == true) {
            $viewPath .=  $template . DIRECTORY_SEPARATOR;
            $paginateFile = $themePath . 'paginate_' . $template . '.php';
        }

        //使用容器修改
        View::config('view_path', $viewPath);

        //如果分页配置存在时，加载分页配置
        if (file_exists($paginateFile)) {
            Config::load($paginateFile, 'paginate');
        }
    }
}