<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-04-23
 * Time: 14:48
 */
namespace addons\test;	// 注意命名空间规范

use think\Addons;

/**
 * 插件测试
 */
class Test extends Addons	// 需继承think\addons\Addons类
{
    // 该插件的基础信息
    public $info = [
        'name' => 'test',    // 插件标识
        'title' => 'Test插件',    // 插件名称
        'description' => 'Test插件，用于测试、调试或试验插件',    // 插件简介
        'status' => 1,    // 状态
        'author' => 'beyongx',
        'version' => '0.1'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $sqls = [

        ];
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     *  hook实现的方法调用，或面板UI返回
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    public function testWidget($param)
    {
        // 调用钩子时候的参数信息
        //print_r($param);
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        //print_r($this->getConfig());
        // 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
        return $this->fetch('view/info');
    }

    //钩子方法不存在，执行插件类:run方法
    public function run($param)
    {
        echo 'execute run method, maybe hook method not exists.';
        //cache('addons', null);
    }
}