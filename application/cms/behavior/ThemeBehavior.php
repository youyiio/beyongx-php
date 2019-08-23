<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-06-12
 * Time: 15:18
 */

namespace app\cms\behavior;

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
        $config = Config::pull('theme');

        /*根据配置和来访设备类型自动切换为电脑主题或手机主题。 start */
        //$modulePath = request()->module();//获取模块名称
        $header = request()->header();
        $isWechat = isset($header['user-agent']) && preg_match('/micromessenger/', strtolower($header['user-agent']));
        if (request()->isMobile() || $isWechat) {
            $template = "mobile";
        } else {
            $template = "pc";
        }

        //设置所有主题的存放路径
        $themePath = Env::get('root_path')  . 'theme' . DIRECTORY_SEPARATOR . $config['current_theme'] . DIRECTORY_SEPARATOR;
        if (isset($config['adaptive']) && $config['adaptive'] == true) {
            $themePath .= $template . DIRECTORY_SEPARATOR;
        }
        //使用容器修改
        View::config('view_path', $themePath);

        //如果分页配置存在时，加载分页配置
        $paginateFile = $themePath . 'paginate.php';
        if (file_exists($paginateFile)) {
            Config::load($paginateFile, 'paginate');
        }
    }
}