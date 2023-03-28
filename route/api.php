<?php

use think\facade\Route;


Route::group('api', function () {
    //通用公共接口
    Route::rule('config/query', 'api/Config/query', 'post');
    Route::rule('config/:name/status', 'api/Config/status', 'get');
    Route::rule("ad/carousel", 'api/Ad/carousel', 'get');
    Route::rule('dept/dict', 'api/Dept/dict', 'get');
    Route::rule('job/dict', 'api/Job/dict', 'get');
    Route::rule('image/upload', 'api/Upload/image', 'post');
    Route::rule('file/upload', 'api/Upload/file', 'post');
    Route::rule('dataQuery/areas', 'api/DataQuery/areas', 'post');
    Route::rule('user/quickSelect', 'api/User/quickSelect', 'get|post');

    //登录注册相关
    Route::rule("sign/login", 'api/Sign/login', 'post|get');
    Route::rule("sign/register", 'api/Sign/register', 'post|get');
    Route::rule("sign/logout", 'api/Sign/logout', 'post|get');
    Route::rule("sign/reset", 'api/Sign/reset', 'post|get');

    //用户管理相关
    Route::rule("user/:id", 'api/User/query', 'get');
    Route::rule("user/list", 'api/User/list', 'get|post');
    Route::rule("user/create", 'api/User/create', 'post');
    Route::rule("user/edit", 'api/User/edit', 'post');
    Route::rule("user/:id", 'api/User/delete', 'delete');
    Route::rule("user/modifyPassword", 'api/User/modifyPassword', 'post');
    Route::rule("user/freeze", 'api/User/freeze', 'post');
    Route::rule("user/unfreeze", 'api/User/unfreeze', 'post');
    Route::rule("user/addRoles", 'api/User/addRoles', 'post');

    //角色管理相关
    Route::rule("role/list", 'api/Role/list', 'get|post');
    Route::rule("role/create", 'api/Role/create', 'post');
    Route::rule("role/edit", 'api/Role/edit', 'post');
    Route::rule("role/:id", 'api/Role/delete', 'delete');
    Route::rule("role/menus/:id", 'api/Role/menus', 'get');
    Route::rule("role/addMenus/:id", 'api/Role/addMenus', 'post');
    Route::rule("role/users/:id", 'api/Role/users', 'get');
    
    //文章管理相关
    Route::rule("article/list", 'api/Article/list', 'get|post');
    Route::rule("article/create", 'api/Article/create', 'post');
    Route::rule("article/:aid", 'api/Article/query', 'get');    
    Route::rule("article/:aid", 'api/Article/edit', 'post');
    Route::rule("article/delete", 'api/Article/delete', 'delete');
    Route::rule("article/publish", 'api/Article/publish', 'post');
    Route::rule("article/audit", 'api/Article/audit', 'post');
    Route::rule("article/comments/:id", 'api/Article/comments', 'get|post');

    //评论相关
    Route::rule("comment/list", 'api/Comment/list', 'get|post');
    Route::rule("comment/:id", 'api/Comment/query', 'get');
    Route::rule("comment/create", 'api/Comment/create', 'post');
    Route::rule("comment/audit", 'api/Comment/audit', 'post');
    Route::rule("comment/delete", 'api/Comment/delete', 'delete');

    //个人中心相关
    Route::rule("ucenter/getInfo", 'api/Ucenter/getInfo', 'get');
    Route::rule("ucenter/profile", 'api/Ucenter/profile', 'post');
    Route::rule("ucenter/menus", 'api/Ucenter/menus', 'get');
    Route::rule("ucenter/modifyPassword", 'api/Ucenter/modifyPassword', 'post');

    //文章分类相关
    Route::rule("category/list", 'api/Category/list', 'get|post');
    Route::rule("category/create", 'api/Category/create', 'post');
    Route::rule("category/edit", 'api/Category/edit', 'post');
    Route::rule("category/setStatus", 'api/Category/setStatus', 'post');
    Route::rule("category/:id", 'api/Category/delete', 'delete');

    //广告相关
    Route::rule("ad/list", 'api/Ad/list', 'get|post');
    Route::rule("ad/slots", 'api/Ad/slots', 'get');
    Route::rule("ad/create", 'api/Ad/create', 'post');
    Route::rule("ad/edit", 'api/Ad/edit', 'post');
    Route::rule("ad/:id", 'api/Ad/delete', 'delete');

    Route::rule("sms/sendCode", 'api/Sms/sendCode', 'post');
    Route::rule("sms/login", 'api/Sms/login', 'post');

    //运维管理相关
    Route::rule("server/status", 'api/Server/status', 'get|post');
    Route::rule("log/list", 'api/Log/list', 'get|post');
    Route::rule("db/tables", 'api/Database/tables', 'get|post');

    //友链相关
    Route::rule("link/list", 'api/Link/list', 'get|post');
    Route::rule("link/create", 'api/Link/create', 'post');
    Route::rule("link/edit", 'api/Link/edit', 'post');
    Route::rule("link/:id", 'api/Link/delete', 'delete');

    //菜单管理相关
    Route::rule("menu/list", 'api/Menu/list', 'get|post');
    Route::rule("menu/create", 'api/Menu/create', 'post');
    Route::rule("menu/edit", 'api/Menu/edit', 'post');
    Route::rule("menu/:id", 'api/Menu/delete', 'delete');

    //部门管理相关
    Route::rule("dept/list", 'api/Dept/list', 'get|post');
    Route::rule("dept/create", 'api/Dept/create', 'post');
    Route::rule("dept/edit", 'api/Dept/edit', 'post');
    Route::rule("dept/:id", 'api/Dept/delete', 'delete');

    //岗位管理相关
    Route::rule("job/list", 'api/Job/list', 'get|post');
    Route::rule("job/create", 'api/Job/create', 'post');
    Route::rule("job/edit", 'api/Job/edit', 'post');
    Route::rule("job/:id", 'api/Job/delete', 'delete');

    //字典管理相关
    Route::rule("config/list", 'api/Config/list', 'get|post');
    Route::rule("config/groups", 'api/Config/groups', 'post');
    Route::rule("config/create", 'api/Config/create', 'post');
    Route::rule("config/edit", 'api/Config/edit', 'post');
    Route::rule("config/:id", 'api/Config/delete', 'delete');

    //移动端通用公共接口
    Route::rule('app/config/:name/status', 'api/app.Config/status', 'get');
    Route::rule('app/config/base', 'api/app.Config/base', 'get');
    Route::rule('app/carousel', 'api/Ad/carousel', 'post');

    //移动端公共管理相关
    Route::rule('app/article/timeline', 'api/app.Article/timeline', 'get|post');
    Route::rule('app/article/latest', 'api/app.Article/latest', 'get|post');
    Route::rule('app/article/hottest', 'api/app.Article/hotTest', 'get|post');
    Route::rule('app/article/:aid', 'api/app.Article/query', 'get');
    Route::rule('app/article/comments/:aid', 'api/app.Article/comments', 'get|post');
    Route::rule('app/article/related/:aid', 'api/app.Article/related', 'get|post');
    Route::rule('app/category/list', 'api/app.Article/categoryList', 'get|post');
    Route::rule('app/link/list', 'api/app.Article/linkList', 'get|post');
    
    // 定义miss路由
    Route::miss('api/Base/miss');


})->ext(false)->header('Access-Control-Allow-Headers', 'token')
->allowCrossDomain()->pattern(['aid' => '\d+']);