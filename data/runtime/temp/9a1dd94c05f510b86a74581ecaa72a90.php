<?php /*a:4:{s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\index\index.html";i:1556273981;s:70:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\left_nav.html";i:1556245321;s:69:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\top_nav.html";i:1556245321;s:75:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\options_theme.html";i:1540972230;}*/ ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title>首页</title>

    <meta name="keywords" content="<?php echo get_config('site_name'); ?>">
    <meta name="description" content="<?php echo get_config('site_name'); ?>">

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/static/admin/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/static/admin/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/static/admin/css/animate.min.css" rel="stylesheet">
    <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
    <div id="wrapper">

        <!--左侧导航开始-->
        <nav class="navbar-default navbar-static-side" role="navigation">
    <div class="nav-close"><i class="fa fa-times-circle"></i>
    </div>
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span><img alt="image" class="img-circle" src="<?php echo htmlentities((isset($myself['head_url']) && ($myself['head_url'] !== '')?$myself['head_url']:'/static/admin/img/profile_small.jpg')); ?>" width="48" height="48"/></span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs"><strong class="font-bold"><?php echo get_config('site_name','管理后台'); ?></strong></span>
                            <span class="text-muted text-xs block"><?php echo htmlentities($myself['nickname']); ?><b class="caret"></b></span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="J_menuItem" onclick="$('.profile-element a.dropdown-toggle').click();" href="<?php echo url('Person/profile',['uid'=>$myself['user_id']]); ?>">个人资料</a>
                        </li>
                        <li><a class="J_menuItem" onclick="$('.profile-element a.dropdown-toggle').click();" href="javascript:void(0)">修改头像</a>
                        </li>
                        <li><a class="J_menuItem" onclick="$('.profile-element a.dropdown-toggle').click();" href="<?php echo url('Person/password',['uid'=>$myself['user_id']]); ?>">修改密码</a>
                        </li>
                        <li class="divider"></li>
                        <li><a onclick="$('.profile-element a.dropdown-toggle').click();" href="<?php echo url('Sign/logout'); ?>">安全退出</a>
                        </li>
                    </ul>
                </div>
                <div class="logo-element"><?php echo get_config('site_name','管理后台'); ?>
                </div>
            </li>

            <?php if(!(empty($menus) || (($menus instanceof \think\Collection || $menus instanceof \think\Paginator ) && $menus->isEmpty()))): if(is_array($menus) || $menus instanceof \think\Collection || $menus instanceof \think\Paginator): if( count($menus)==0 ) : echo "" ;else: foreach($menus as $key=>$menu): if(empty($menu['_data']) || (($menu['_data'] instanceof \think\Collection || $menu['_data'] instanceof \think\Paginator ) && $menu['_data']->isEmpty())): ?>
            <li class="<?php echo menu_select($menu['name']); ?>" class="leftSubNav">
                <a class="J_menuItem" href="<?php echo url($menu['name']); ?>"><?php if(!(empty($menu['icon']) || (($menu['icon'] instanceof \think\Collection || $menu['icon'] instanceof \think\Paginator ) && $menu['icon']->isEmpty()))): ?><i class="fa <?php echo htmlentities($menu['icon']); ?>"></i><?php endif; ?> <span class="nav-label"><?php echo htmlentities($menu['title']); ?></span></a>
            </li>
            <?php else: ?>
            <li>
                <a href="#"><?php if(!(empty($menu['icon']) || (($menu['icon'] instanceof \think\Collection || $menu['icon'] instanceof \think\Paginator ) && $menu['icon']->isEmpty()))): ?><i class="fa <?php echo htmlentities($menu['icon']); ?>"></i><?php endif; ?> <span class="nav-label"><?php echo htmlentities($menu['title']); ?> </span>
                    <?php if($menu['id'] == 3): ?>
                    <span class="label label-warning pull-right"><?php echo user_count('new'); ?></span>
                    <?php else: ?>
                    <span class="fa arrow"></span>
                    <?php endif; ?>
                </a>
                <ul class="nav nav-second-level collapse">
                    <?php if(is_array($menu['_data']) || $menu['_data'] instanceof \think\Collection || $menu['_data'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu['_data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                    <li>
                        <a class="J_menuItem" href="<?php echo url($sub['name']); ?>"><?php echo htmlentities($sub['title']); ?></a>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <?php endif; ?>

        </ul>
    </div>
</nav>
        <!--左侧导航结束-->

        <!--右侧部分开始-->
        <div id="page-wrapper" class="gray-bg dashbard-1">
            <!--
<div class="row border-bottom">
  <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
      <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
      <form role="search" class="navbar-form-custom" action="<?php echo url('User/index'); ?>">
        <div class="form-group">
          <input type="text" placeholder="输入用户邮箱" class="form-control" name="key" id="top-search">
          <input type="hidden" class="form-control" name="type" value="email">
        </div>
      </form>
    </div>
    <ul class="nav navbar-top-links navbar-right">

      <li>
        <a href="http://www.<?php echo config('app.url_domain_root'); ?>" target="_blank">
          <i class="fa fa-home"></i> 返回前台
        </a>
      </li>
      <li>
        <a href="<?php echo url('Login/logout'); ?>">
          <i class="fa fa-sign-out"></i> 退出
        </a>
      </li>

    </ul>

  </nav>
</div>
-->


<div class="row border-bottom">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
            <form role="search" class="navbar-form-custom" method="get" action="<?php echo url('User/index'); ?>">
                <div class="form-group">
                    <input type="hidden" name="type" value="email"/>
                    <input type="text" placeholder="请输入您需要查找的内容…" class="form-control" name="key" id="top-search" autocomplete="off">
                </div>
            </form>
        </div>
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <a href="<?php echo url('cms/Index/index', [], true, get_config('domain_name')); ?>" target="_blank">
                    <i class="fa fa-home"></i> 网站前台
                </a>
            </li>

            <!--
            <li class="dropdown">
              <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                <i class="fa fa-envelope"></i> <span class="label label-warning">16</span>
              </a>
              <ul class="dropdown-menu dropdown-messages">
                <li class="m-t-xs">
                  <div class="dropdown-messages-box">
                    <a href="profile.html" class="pull-left">
                      <img alt="image" class="img-circle" src="/static/admin/img/a7.jpg">
                    </a>
                    <div class="media-body">
                      <small class="pull-right">46小时前</small>
                      <strong>小四</strong> 这个在日本投降书上签字的军官，建国后一定是个不小的干部吧？
                      <br>
                      <small class="text-muted">3天前 2014.11.8</small>
                    </div>
                  </div>
                </li>
                <li class="divider"></li>
                <li>
                  <div class="dropdown-messages-box">
                    <a href="profile.html" class="pull-left">
                      <img alt="image" class="img-circle" src="/static/admin/img/a4.jpg">
                    </a>
                    <div class="media-body ">
                      <small class="pull-right text-navy">25小时前</small>
                      <strong>国民岳父</strong> 如何看待“男子不满自己爱犬被称为狗，刺伤路人”？——这人比犬还凶
                      <br>
                      <small class="text-muted">昨天</small>
                    </div>
                  </div>
                </li>
                <li class="divider"></li>
                <li>
                  <div class="text-center link-block">
                    <a class="J_menuItem" href="mailbox.html">
                      <i class="fa fa-envelope"></i> <strong> 查看所有消息</strong>
                    </a>
                  </div>
                </li>
              </ul>
            </li>
            -->

            <li class="dropdown">
                <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#" id="allCount">
                    <i class="fa fa-bell"></i> <span class="label label-primary"><?php echo message_count(0, \app\common\model\MessageModel::STATUS_SEND); ?></span>
                </a>
                <?php if(message_count(0, \app\common\model\MessageModel::STATUS_SEND) > 0): ?>
                <ul class="dropdown-menu dropdown-alerts">
                    <?php if(message_count(\app\common\model\MessageModel::TYPE_SYSTEM, \app\common\model\MessageModel::STATUS_SEND) > 0): ?>
                    <li id="system">
                        <a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>">
                            <div>
                                <i class="fa fa-envelope fa-fw"></i> 您有<?php echo message_count(\app\common\model\MessageModel::TYPE_SYSTEM, \app\common\model\MessageModel::STATUS_SEND); ?>条未读系统消息
                                <span class="pull-right text-muted small">4分钟前</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <?php endif; if(message_count(\app\common\model\MessageModel::TYPE_COMMENT, \app\common\model\MessageModel::STATUS_SEND) > 0): ?>
                    <li id="comment">
                        <a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>">
                            <div>
                                <i class="fa fa-qq fa-fw"></i> <?php echo message_count(\app\common\model\MessageModel::TYPE_COMMENT, \app\common\model\MessageModel::STATUS_SEND); ?>条新回复
                                <span class="pull-right text-muted small">12分钟钱</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <?php endif; if(message_count(\app\common\model\MessageModel::TYPE_MAIL, \app\common\model\MessageModel::STATUS_SEND) > 0): ?>
                    <li id="mail">
                        <a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>">
                            <div>
                                <i class="fa fa-envelope fa-fw"></i> 您有<?php echo message_count(\app\common\model\MessageModel::TYPE_MAIL, \app\common\model\MessageModel::STATUS_SEND); ?>条未读站内消息
                                <span class="pull-right text-muted small">4分钟前</span>
                            </div>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <?php endif; ?>

                    <li>
                        <div class="text-center link-block">
                            <a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>">
                                <strong>查看所有 </strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </div>
                    </li>
                </ul>
                <?php endif; ?>
            </li>
            <!--
            <li class="hidden-xs">
              <a href="#" class="J_menuItem" data-index="0"><i class="fa fa-cart-arrow-down"></i> 购买</a>
            </li>
            -->
            <li class="hidden-xs">
                <a href="<?php echo url('admin/Sign/logout'); ?>" class="J_tabExit" data-index="1"><i class="fa fa fa-sign-out"></i> 退出</a>
            </li>
            <!--
            <li class="dropdown hidden-xs">
              <a class="right-sidebar-toggle" aria-expanded="false">
                <i class="fa fa-tasks"></i> 主题
              </a>
            </li>
            -->
        </ul>
    </nav>
</div>

<script>
    function message() {
        $.ajax({
            url: '<?php echo url("admin/Index/index"); ?>',
            type: 'get',
            async: true,
            dataType: 'json',
            success: function (data) {
                var allCount = '<i class="fa fa-bell"></i><span class="label label-primary">' + data.data["allCount"] + ' </span>';
                var system = '<a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>"><div><i class="fa fa-envelope fa-fw"></i> 您有' + data.data["systemCount"] + '条未读系统消息<span class="pull-right text-muted small">12分钟钱</span></div></a>';
                var comment = '<a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>" data-tab-name="评论管理"><div><i class="fa fa-qq fa-fw"></i>' + data.data["commentCount"] + '条新回复<span class="pull-right text-muted small">12分钟钱</span></div></a>';
                var mail = '<a class="J_menuItem" href="<?php echo url('Article/commentList'); ?>"><div><i class="fa fa-qq fa-fw"></i> 您有' + data.data["mailCount"] + '条未读站内消息<span class="pull-right text-muted small">12分钟钱</span></div></a>';

                $("#allCount").html(allCount);
                $("#system").html(system);
                $("#comment").html(comment);
                $("#mail").html(mail);
            },
            error: function () {

            }
        });
    }

    setInterval(message, 5000);
</script>

            <div class="row content-tabs">
                <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
                </button>
                <nav class="page-tabs J_menuTabs">
                    <div class="page-tabs-content">
                        <a href="javascript:;" class="active J_menuTab" data-id="<?php echo url('index/welcome'); ?>">首页</a>
                    </div>
                </nav>
                <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
                </button>
                <div class="btn-group roll-nav roll-right">
                    <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span>

                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li class="J_tabShowActive"><a>定位当前选项卡</a>
                        </li>
                        <li class="divider"></li>
                        <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                        </li>
                        <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                        </li>
                    </ul>
                </div>
                <a href="javascript:void(0)" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-long-arrow-left"></i> 返回</a>
            </div>

            <div class="row J_mainContent" id="content-main">
                <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<?php echo url('index/welcome'); ?>" frameborder="0" data-id="<?php echo url('index/welcome'); ?>" seamless></iframe>
            </div>
            <div class="footer">
                <div class="pull-right">&copy; 2017-<?php echo date('Y'); ?> <a href="<?php echo get_config('domain_name'); ?>" target="_blank"><?php echo get_config('site_name'); ?></a>
                </div>
            </div>
        </div>
        <!--右侧部分结束-->

        <!--右侧边栏开始-->
        <div id="right-sidebar">
    <div class="sidebar-container">

        <ul class="nav nav-tabs navs-3">

            <li class="active">
                <a data-toggle="tab" href="#tab-1">
                    <i class="fa fa-gear"></i> 主题
                </a>
            </li>
            <li class=""><a data-toggle="tab" href="#tab-2">
                通知
            </a>
            </li>
            <li><a data-toggle="tab" href="#tab-3">
                项目进度
            </a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="sidebar-title">
                    <h3> <i class="fa fa-comments-o"></i> 主题设置</h3>
                    <small><i class="fa fa-tim"></i> 你可以从这里选择和预览主题的布局和样式，这些设置会被保存在本地，下次打开的时候会直接应用这些设置。</small>
                </div>
                <div class="skin-setttings">
                    <div class="title">主题设置</div>
                    <div class="setings-item">
                        <span>收起左侧菜单</span>
                        <div class="switch">
                            <div class="onoffswitch">
                                <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                                <label class="onoffswitch-label" for="collapsemenu">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="setings-item">
                        <span>固定顶部</span>

                        <div class="switch">
                            <div class="onoffswitch">
                                <input type="checkbox" name="fixednavbar" class="onoffswitch-checkbox" id="fixednavbar">
                                <label class="onoffswitch-label" for="fixednavbar">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="setings-item">
                                <span>
                        固定宽度
                    </span>

                        <div class="switch">
                            <div class="onoffswitch">
                                <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                                <label class="onoffswitch-label" for="boxedlayout">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="title">皮肤选择</div>
                    <div class="setings-item default-skin nb">
                                <span class="skin-name ">
                         <a href="#" class="s-skin-0">
                             默认皮肤
                         </a>
                    </span>
                    </div>
                    <div class="setings-item blue-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-1">
                            蓝色主题
                        </a>
                    </span>
                    </div>
                    <div class="setings-item yellow-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-3">
                            黄色/紫色主题
                        </a>
                    </span>
                    </div>
                </div>
            </div>
            <div id="tab-2" class="tab-pane">

                <div class="sidebar-title">
                    <h3> <i class="fa fa-comments-o"></i> 最新通知</h3>
                    <small><i class="fa fa-tim"></i> 您当前有10条未读信息</small>
                </div>

                <div>

                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a1.jpg">

                                <div class="m-t-xs">
                                    <i class="fa fa-star text-warning"></i>
                                    <i class="fa fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="media-body">

                                据天津日报报道：瑞海公司董事长于学伟，副董事长董社轩等10人在13日上午已被控制。
                                <br>
                                <small class="text-muted">今天 4:21</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a2.jpg">
                            </div>
                            <div class="media-body">
                                HCY48之音乐大魔王会员专属皮肤已上线，快来一键换装拥有他，宣告你对华晨宇的爱吧！
                                <br>
                                <small class="text-muted">昨天 2:45</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a3.jpg">

                                <div class="m-t-xs">
                                    <i class="fa fa-star text-warning"></i>
                                    <i class="fa fa-star text-warning"></i>
                                    <i class="fa fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="media-body">
                                写的好！与您分享
                                <br>
                                <small class="text-muted">昨天 1:10</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a4.jpg">
                            </div>

                            <div class="media-body">
                                国外极限小子的炼成！这还是亲生的吗！！
                                <br>
                                <small class="text-muted">昨天 8:37</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a8.jpg">
                            </div>
                            <div class="media-body">

                                一只流浪狗被收留后，为了减轻主人的负担，坚持自己觅食，甚至......有些东西，可能她比我们更懂。
                                <br>
                                <small class="text-muted">今天 4:21</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a7.jpg">
                            </div>
                            <div class="media-body">
                                这哥们的新视频又来了，创意杠杠滴，帅炸了！
                                <br>
                                <small class="text-muted">昨天 2:45</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a3.jpg">

                                <div class="m-t-xs">
                                    <i class="fa fa-star text-warning"></i>
                                    <i class="fa fa-star text-warning"></i>
                                    <i class="fa fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="media-body">
                                最近在补追此剧，特别喜欢这段表白。
                                <br>
                                <small class="text-muted">昨天 1:10</small>
                            </div>
                        </a>
                    </div>
                    <div class="sidebar-message">
                        <a href="#">
                            <div class="pull-left text-center">
                                <img alt="image" class="img-circle message-avatar" src="/static/admin/img/a4.jpg">
                            </div>
                            <div class="media-body">
                                我发起了一个投票 【你认为下午大盘会翻红吗？】
                                <br>
                                <small class="text-muted">星期一 8:37</small>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
            <div id="tab-3" class="tab-pane">

                <div class="sidebar-title">
                    <h3> <i class="fa fa-cube"></i> 最新任务</h3>
                    <small><i class="fa fa-tim"></i> 您当前有14个任务，10个已完成</small>
                </div>

                <ul class="sidebar-list">
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>市场调研</h4> 按要求接收教材；

                            <div class="small">已完成： 22%</div>
                            <div class="progress progress-mini">
                                <div style="width: 22%;" class="progress-bar progress-bar-warning"></div>
                            </div>
                            <div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>可行性报告研究报上级批准 </h4> 编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

                            <div class="small">已完成： 48%</div>
                            <div class="progress progress-mini">
                                <div style="width: 48%;" class="progress-bar"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>立项阶段</h4> 东风商用车公司 采购综合综合查询分析系统项目进度阶段性报告武汉斯迪克科技有限公司

                            <div class="small">已完成： 14%</div>
                            <div class="progress progress-mini">
                                <div style="width: 14%;" class="progress-bar progress-bar-info"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <span class="label label-primary pull-right">NEW</span>
                            <h4>设计阶段</h4>
                            <!--<div class="small pull-right m-t-xs">9小时以后</div>-->
                            项目进度报告(Project Progress Report)
                            <div class="small">已完成： 22%</div>
                            <div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>拆迁阶段</h4> 科研项目研究进展报告 项目编号: 项目名称: 项目负责人:

                            <div class="small">已完成： 22%</div>
                            <div class="progress progress-mini">
                                <div style="width: 22%;" class="progress-bar progress-bar-warning"></div>
                            </div>
                            <div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>建设阶段 </h4> 编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

                            <div class="small">已完成： 48%</div>
                            <div class="progress progress-mini">
                                <div style="width: 48%;" class="progress-bar"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="small pull-right m-t-xs">9小时以后</div>
                            <h4>获证开盘</h4> 编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

                            <div class="small">已完成： 14%</div>
                            <div class="progress progress-mini">
                                <div style="width: 14%;" class="progress-bar progress-bar-info"></div>
                            </div>
                        </a>
                    </li>

                </ul>

            </div>
        </div>

    </div>
</div>
        <!--右侧边栏结束-->

    </div>

    <script src="/static/inspinia/js/bootstrap.min.js"></script>
    <script src="/static/inspinia/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/static/inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="/static/inspinia/js/plugins/layer/layer.min.js"></script>
    <script src="/static/admin/js/hplus.min.js?v=4.1.0"></script>
    <script src="/static/admin/js/contabs.min.js" type="text/javascript"></script>
    <script src="/static/inspinia/js/plugins/pace/pace.min.js"></script>

    <script>
        $(".J_tabExit").click(function() {
            var id = $(".J_menuTab.active").data("id");
            var $iframe = $('.J_iframe[data-id="' + id + '"]');
            //IE下contentWindow可以忽略
            var $history = $iframe.get(0).contentWindow.history;
            //console.log($history.length);
            if ($history.length > 0) {
                $history.back()
            }
        })
    </script>
</body>
</html>
