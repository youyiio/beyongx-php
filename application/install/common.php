<?php

define('INSTALL_APP_PATH', \think\facade\Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR);

/**
 * 系统环境检测
 * @author beyongx
 * @return array
 */
function check_env()
{
    $items = array(
        'os'      => array('操作系统', '不限制', '类Unix', PHP_OS, 'check'),
        'php'     => array('PHP版本', '5.6', '7.0+', PHP_VERSION, 'check'),
        'upload'  => array('附件上传', '不限制', '8M+', '未知', 'check'),
        'gd'      => array('GD库', '2.0', '2.0+', '未知', 'check'),
        'disk'    => array('磁盘空间', '60M', '不限制', '未知', 'check'),
    );

    //PHP环境检测
    if ($items['php'][3] < $items['php'][1]) {
        $items['php'][4] = 'times';
    }

    //附件上传检测
    if (@ini_get('file_uploads')) {
        $items['upload'][3] = ini_get('upload_max_filesize');
    }

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if (empty($tmp['GD Version'])) {
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'times';
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }

    unset($tmp);

    //磁盘空间检测
    if (function_exists('disk_free_space')) {
        $items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024)).'M';
    }

    return $items;
}


/**
 * 目录，文件读写检测
 * @author beyongx
 * @return array
 */
function check_dir_chmod()
{
    $items = array(
        array('dir',  '可写', 'check', './upload'),
        array('dir',  '可写', 'check', '../data'),
        array('dir',  '可写', 'check', '../data/runtime'),
    );

    foreach ($items as &$val) {
        $item =	INSTALL_APP_PATH . $val[3];

        if ('dir' == $val[0]) {
            if (!is_writable($item)) {
                if (is_dir($item)) {
                    $val[1] = '可读';
                    $val[2] = 'minus';
                } else {
                    $val[1] = '不存在';
                    $val[2] = 'times';
                }
            }
        } else {
            if (file_exists($item)) {
                if (!is_writable($item)) {
                    $val[1] = '不可写';
                    $val[2] = 'minus';
                }
            } else {
                if (!is_writable(dirname($item))) {
                    $val[1] = '不存在';
                    $val[2] = 'minus';
                }
            }
        }
    }

    return $items;
}


/**
 * 函数检测
 * @author beyongx
 * @return array
 */
function check_func()
{
    $items = array(
        array('pdo','支持','check','类'),
        array('pdo_mysql','支持','check','模块'),
        //array('redis','支持','check','模块'),
        array('openssl_sign','支持','check','函数'),
        array('file_get_contents', '支持', 'check','函数'),
    );

    foreach ($items as &$val) {
        if (('类' == $val[3] && !class_exists($val[0])) || ('模块'==$val[3] &&
            !extension_loaded($val[0])) || ('函数'==$val[3] && !function_exists($val[0]))) {
            $val[1] = '不支持';
            $val[2] = 'times';
        }
    }

    return $items;
}


/**
 * 创建数据表
 *
 * @author beyongx
 *
 * @param $dbConnect
 * @param string $prefix
 *
 * @return bool
 */
function create_tables($dbConnect, $prefix = '')
{
    $result = true;

    //读取SQL文件
    $sql = file_get_contents(\think\facade\Env::get('root_path') . 'data/install/install.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);
    //替换表前缀
    $oldPrefix = 'cms_';

    $sql = str_replace(" {$oldPrefix}", " {$prefix}", $sql);

    //开始安装
    foreach ($sql as $value) {
        $value = trim($value);
        \think\facade\Log::debug($value);
        if (empty($value)) {
            continue;
        }

        if (false === $dbConnect->execute($value)) {
            $result = false;
            die("error");
        }
    }

    return $result;
}


/**
 * 生成系统password_key
 * @author beyongx
 * @return bool|string
 */
function build_password_key()
{
    $chars  = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chars .= '!@#$%^&*()_+-[]{};:|,.<>/?';
    $chars  = str_shuffle($chars);
    return substr($chars, 0, 24);
}


/**
 * 站点配置
 *
 * @author beyongx
 * @param $dbConnect
 * @param $prefix
 * @param $site
 *
 * @return bool
 */
function update_config($dbConnect, $prefix, $site)
{
    //增加更新的password_key
    $site['password_key'] = build_password_key();
    session('site', $site);
    \think\facade\Log::debug($site);
    foreach ($site as $k => $v) {
        \think\facade\Log::debug('config: ' . $k . '=' . $v);
        $sql = "UPDATE sys_config SET value = '". $v . "' WHERE name = '". $k ."'";
        $dbConnect->execute($sql);
    }

    return true;
}

/**
 * 创建管理员
 *
 * @author beyongx
 *
 * @param $dbConnect
 * @param $prefix
 * @param $admin
 *
 * @return mixed
 */
function update_admin($dbConnect, $prefix, $admin)
{
    $site = session('site');
    $passwordKey = $site['password_key'];
    $password = encrypt_password($admin['password'], $passwordKey);
    $time = date_time();

    $email = $admin['email'];
    $username = $admin['username'];
    $sql = "UPDATE sys_user SET email = '". $email . "',password = '". $password . "',account='" . $username . "' WHERE email = 'admin@admin.com'";

    //执行sql
    return $dbConnect->execute($sql);
}

/**
 * 写入配置文件
 *
 * @author beyongx
 * @param $config
 * @return bool
 */
function write_config_files($config)
{

    //读取数据库配置内容
    $tpl = file_get_contents(\think\facade\Env::get('root_path') . 'data/install/database.tpl');

    //替换配置项
    foreach ($config as $name => $value) {
        if (is_array($value)) {
            continue;
        }

        $tpl = str_replace("[{$name}]", $value, $tpl);
    }

    $configPath = \think\facade\Env::get('root_path') . 'data/install/database.php';
    if (file_put_contents($configPath, $tpl)) {
        // 写入安装锁定文件(只能在最后一步写入锁定文件，因为锁定文件写入后安装模块将无法访问)
        file_put_contents(\think\facade\Env::get('root_path') . 'data/install.lock',  date_time());

        return true;
    }

    return false;
}

/**
 *
 * @author beyongx
 * @param $dir
 * @return bool
 */
function delete_dir($dir)
{
    if (!$handle = @opendir($dir)) {
        return false;
    }
    while (false !== ($file = readdir($handle))) {
        if ($file !== "." && $file !== "..") {       //排除当前目录与父级目录
            $file = $dir . '/' . $file;
            if (is_dir($file)) {
                delete_dir($file);
            } else {
                @unlink($file);
            }
        }

    }
    return @rmdir($dir);
}