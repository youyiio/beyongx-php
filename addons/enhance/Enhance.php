<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-06-12
 * Time: 10:54
 */
namespace addons\enhance;


class Enhance extends \think\Addons
{
    use \app\common\controller\AdminBase; //使用trait

    // 该插件的基础信息
    public $config = [
        'name' => 'enhance',    // 插件标识
        'title' => '系统增强插件',    // 插件名称
        'description' => 'Cms系统增强插件,用于前后部分定制',    // 插件简介
        'status' => 1,    // 状态
        'author' => 'beyongx.com',
        'version' => '0.1'
    ];

    public function initialize()
    {
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    //钩子方法：用户动态
    public function userTimeline($param)
    {
        return $this->fetch('view/user_timeline');
    }

    //钩子方法：用户帐户信息，与钱或积分相关
    public function userBalance($param)
    {
        return $this->fetch('view/user_balance');
    }

    //钩子方法：用户配置相关
    public function userConfig($param)
    {
        return $this->fetch('view/user_config');
    }

    //钩子方法：用户相关列表，与系统有关的数据列表
    public function userBusiness01($param)
    {
        return $this->fetch('view/user_business_01');
    }
}