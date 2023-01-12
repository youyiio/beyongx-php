<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-06-12
 * Time: 15:18
 */

namespace app\frontend\behavior;

use think\facade\Config;
use think\facade\Env;
use think\facade\View;

/**
 * 主题设置，主题切换
 * Class ThemeBehavior
 * @package app\cms\behavior
 */
class ThemeBehavior
{
    public function run()
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