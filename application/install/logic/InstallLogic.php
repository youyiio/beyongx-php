<?php
namespace app\install\logic;

use think\Db;
use think\facade\Log;
use think\Model;

/**
 * 安装逻辑
 */
class InstallLogic extends Model
{

    /**
     * 检查站点安装数据
     * @author cattong
     * @param null $site
     * @param null $admin
     * @return bool
     */
    public function checkSiteConfig($site = null, $admin = null)
    {

        // 检测管理员信息
        if (!is_array($admin) || empty($admin['username']) || empty($admin['email']) || empty($admin['password'])) {
            $this->error = '请填写完整管理员信息';
            return false;
        }

        if ($admin['password'] != $admin['repassword']) {
            $this->error = '确认密码和密码不一致';
            return false;
        }

        $site['title'] = $site['site_name'];

        session('site', $site);
        session('admin', $admin);

        return true;
    }


    /**
     * 检查数据库安装数据
     * @author cattong
     * @param null $db
     * @return bool|array
     */
    public function checkDbConfig($db = null)
    {
        // 检测数据库配置
        if (!is_array($db) || empty($db['hostname']) ||  empty($db['hostport']) || empty($db['database']) || empty($db['username'])) {
            $this->error = '请填写完整的数据库配置';
            return false;
        }

        try {
            $db['params'] = [
                \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                \PDO::ATTR_EMULATE_PREPARES => true,
            ];
            $dbConnect = Db::connect($db);
        } catch (\Exception $e) {
            $this->error = '安装出现问题：' . $e->getMessage();
            Log::error('安装出现问题：' . $e->getMessage());
            return false;
        }

        session('db', $db);

        return true;
    }

    /**
     * 检查数据库否安装过
     * @param null $db
     * @return bool
     */
    public function checkInstalled($db = null)
    {
        $installed = false;
        try {
            $db['params'] = [
                \PDO::ATTR_CASE              => \PDO::CASE_LOWER,
                \PDO::ATTR_EMULATE_PREPARES  => true,
            ];
            $dbConnect = Db::connect($db);
            $checkTable = $db['prefix'] . 'user';
            $exist = $dbConnect->query("show tables like '$checkTable'");
            if ($exist) {
                $this->error = '数据库已经安装过，请确认数据库已经清空表';
                $installed = true;
            }
        } catch (\Exception $e) {
            $this->error = '安装出现问题：' . $e->getMessage();
            Log::error('安装出现问题：' . $e->getMessage());
        }

        return $installed;
    }

    /**
     * 开始安装
     * @author cattong
     * @return bool|array
     */
    public function install()
    {

        $success = false;

        $site = session('site');
        $admin = session('admin');
        $db = session('db');
        try {
            //创建数据库

            /* 自动创建数据库
            $dbname = $db['database'];
            unset($db['database']);
            $dbConnect = Db::connect($db);

            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
            if (!$dbConnect->execute($sql)) {
                return [ 'code' => ResultCode::ACTION_FAILED, 'msg' => '创建数据库失败'];
            }

            //创建数据表
            $db['database'] = $database_name;*/

            $db['params'] = [
                \PDO::ATTR_CASE              => \PDO::CASE_LOWER,
                \PDO::ATTR_EMULATE_PREPARES  => true,
            ];
            $dbConnect = Db::connect($db);
            if (!create_tables($dbConnect, $db['prefix'])) {
                $this->error = '创建数据表失败';
                return false;
            }

            //更新数据库站点配置
            if (!update_config($dbConnect, $db['prefix'], $site)) {
                $this->error = '写入数据库站点配置失败';
                return false;
            }

            //更新超级帐号
            if (!update_admin($dbConnect, $db['prefix'], $admin)) {
                $this->error = '注册超级管理员失败';
                return false;
            }

            //创建配置文件
            if (!write_config_files($db)) {
                $this->error = '创建配置文件失败';
                return false;
            }

            $success = true;
        } catch (\Exception $e) {
            $this->error = '安装出现问题：' . $e->getMessage();
            Log::error('安装出现问题：' . $e->getMessage());
            return false;
        }

        return $success;
    }
}
