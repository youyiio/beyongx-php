<?php
/**
 * Cms模块路由，针对seo优化进行设置
 * User: cattong
 * Date: 2019-02-18
 * Time: 11:35
 */

return [

    /*****************Cms 通用路由 begin*******************/
    //首页
    'index/index' => ['cms/Index/index', ['method'=>'get']],
    'index/business' => ['cms/Index/business', ['method'=>'get']],
    'index/team' => ['cms/Index/team', ['method'=>'get']],
    'index/partner' => ['cms/Index/partner', ['method'=>'get']],
    'index/about' => ['cms/Index/about', ['method'=>'get']],
    'index/contact' => ['cms/Index/contact', ['method'=>'get']],
    'index/:page' => ['cms/Index/:page', ['method'=>'get']], //可动态扩充页面

    //文章
    'list/index' => ['cms/Article/index', ['method'=>'get']],
    'list/<cid>' => ['cms/Article/articleList', ['method'=>'get']],
    'list/<cname>' => ['cms/Article/articleList', ['method'=>'get']],

    'article/<aid>' => ['cms/Article/viewArticle', ['method'=>'get']],
    'article/<cname>/<aid>' => ['cms/Article/viewArticle', ['method'=>'get']],

    //站点地图
    'sitemap.xml' => ['cms/Sitemap/index', ['method'=>'get']],

    /*****************Cms 通用路由 end*******************/
];