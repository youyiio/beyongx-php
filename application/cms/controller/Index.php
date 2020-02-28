<?php
namespace app\cms\controller;

use app\common\model\ArticleModel;

class Index extends Base
{
    /**
     * 首页
     * @return \think\response\View
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 业务介绍|解决方案
     * @return \think\response\View
     */
    public function business()
    {
        return $this->fetch('business');
    }

    /**
     * 合作伙伴|合作案例
     * @return \think\response\View
     */
    public function partner()
    {
        return $this->fetch('partner');
    }

    /**
     * 关于我们|自我介绍
     * @return \think\response\View
     */
    public function about()
    {
        return $this->fetch('about');
    }

    /**
     * 联系我们|联系方式
     * @return \think\response\View
     */
    public function contact()
    {
        return $this->fetch('contact');
    }

    /**
     * 加入我们|岗位招聘
     * @return \think\response\View
     */
    public function jobs()
    {
        return $this->fetch('jobs');
    }

    /**
     * 支持index/*, 动态扩展
     * @param $name
     * @return mixed|\think\response\View
     */
    public function __extPage($name)
    {
        //动态方法调用
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), []);
        }

        //如果是静态的，直接显示
        return $this->fetch($name);
    }
}
