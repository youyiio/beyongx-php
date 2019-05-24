<?php
namespace app\cms\controller;

use app\common\model\ArticleModel;

use app\cms\paginator\PcPaginator;

class Index extends Base
{
    /**
     * 首页
     * @return \think\response\View
     */
    public function index()
    {
        return view('index');
    }

    /**
     * 业务介绍|解决方案
     * @return \think\response\View
     */
    public function business()
    {
        return view('business');
    }

    /**
     * 合作伙伴|合作案例
     * @return \think\response\View
     */
    public function partner()
    {
        return view('partner');
    }

    /**
     * 关于我们|自我介绍
     * @return \think\response\View
     */
    public function about()
    {
        return view('about');
    }

    /**
     * 联系我们|联系方式
     * @return \think\response\View
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * 加入我们|岗位招聘
     * @return \think\response\View
     */
    public function jobs()
    {
        return view('jobs');
    }
}
