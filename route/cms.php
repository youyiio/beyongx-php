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
    'index/:name' => ['cms/Index/__extPage', ['method'=>'get']], //可动态扩充页面

    //文章
    'list/index' => ['cms/Article/index', ['method'=>'get']],
    'list/:cid' => ['cms/Article/articleList', ['method'=>'get']],
    'list/:cname/[:csubname]' => ['cms/Article/articleList', ['method'=>'get']], //:cname 为必须参数，csubname加[]为可选参数

    'articles/:cname/:aid' => ['cms/Article/viewArticle', ['method'=>'get']], //与以下规则，不能对换；只匹配最先匹配，而非最优配置
    'article/:aid' => ['cms/Article/viewArticle', ['method'=>'get']],
    'tag/:tag' => ['cms/Article/tag', ['method'=>'get'], ['tag'=>'\w+']],

    //搜索
    'search/[:q]/[:p]' => ['cms/Search/index', ['method' => ['get', 'post']], ['q'=>'\w+', 'p'=>'\d+']],

    //站点地图
    'sitemap.xml' => ['cms/Sitemap/xml', ['method'=>'get']],
    'sitemap-[:id].xml' => ['cms/Sitemap/xml', ['method'=>'get']],
    'sitemap' => ['cms/Sitemap/html', ['method'=>'get']],

    //用户操作
    'sign/index' => ['cms/Sign/index', ['method'=>'get,post']],
    'sign/login' => ['cms/Sign/login', ['method'=>'get,post']],
    'sign/register' => ['cms/Sign/register', ['method'=>'get,post']],
    'sign/logout' => ['cms/Sign/logout', ['method'=>'get,post']],
    'sign/forget' => ['cms/Sign/forget', ['method'=>'get,post']],

    /*****************Cms 通用路由 end*******************/
];