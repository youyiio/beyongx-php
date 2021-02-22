<?php


namespace app\admin\controller;

use app\common\model\ConfigModel;
use app\common\model\FileModel;
use think\facade\Cache;
use think\facade\Env;

/**
 * 主题控制器
 */
class Theme extends Base
{

    public function index()
    {
        //主题存放路径
        $themePath = Env::get('root_path')  . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR;
        $files = scandir($themePath);
        $packageNames = [];
        foreach ($files as $filename) {
            if ($filename == '.' || $filename == '..' || !is_dir($themePath . $filename)) {
                continue;
            }

            $packageNames[] = $filename;
        }

        $themes = [];
        foreach ($packageNames as $packageName) {
            //读取主题详细信息
            $themeFile = $themePath . $packageName . DIRECTORY_SEPARATOR . 'theme.php';
            if (!file_exists($themeFile)) {
                continue;
            }

            $theme = require($themeFile);
            $errorKeys = $this->checkThemeIntegrity($theme);
            if (!empty($errorKeys)) {
                //continue;
            }

            $themes[] = $theme;
        }

//        $themePath = Env::get('root_path')  . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR;
//        $zipFile = $themePath . 'classic.zip';
//        x_zip($themePath . 'classic', $zipFile);

        $this->assign('themes', $themes);

        return $this->fetch('index');
    }

    //切换主题
    public function setCurrentTheme($package_name)
    {
        if (empty($package_name)) {
            $this->error('参数错误');
        }

        $ConfigModel = new ConfigModel();
        $ConfigModel->where('name', 'theme_package_name')->setField('value', $package_name);

        //清空缓存
        Cache::set('config', null);

        $this->success('主题切换成功');
    }

    //上传主题
    public function upload()
    {
        $fileId = input('fileId/d', 0);
        $file = FileModel::get($fileId);
        if (!$file) {
            $this->error('文件不存在！');
        }

        $themePath = Env::get('root_path')  . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR;
        $zipFile = $file['file_path'] . $file['file_url'];
        //dump($themePath);dump($zipFile);die('dd');
        x_unzip($zipFile, $themePath);

        $this->success('主题上传并安装成功！');
    }

    public function market()
    {
        return $this->fetch('market');
    }

    //主题配置完整整性检查
    private function checkThemeIntegrity(&$theme)
    {
        $keys = array_keys($theme);

        $require_keys = ['name', 'package_name', 'responsive', 'article_thumb_image'];

        $error_keys = [];
        foreach ($require_keys as $key) {
            if (!in_array($key, $keys)) {
                $error_keys[] = $key;
                $theme[$key] = '主题 ' . $key . ' 未配置！';
            }
        }

        return $error_keys;
    }
}