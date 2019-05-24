<?php /*a:2:{s:68:"D:\server\wnmp\wwwroot\Cms\application\admin\view\user\viewUser.html";i:1556245321;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 用户详情</title>

  <link rel="shortcut icon" href="favicon.ico">
  <link href="/static/admin/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
  <link href="/static/admin/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">

  <!-- Morris -->
  <link href="/static/admin/css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">

  <!-- Toastr style -->
  <link href="/static/admin/css/plugins/toastr/toastr.min.css" rel="stylesheet">
  <!-- sweet alert -->
  <link href="/static/admin/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
   <!--页面自定义头部样式-->
  

  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row m-b-lg m-t-lg">
        <div class="col-md-4">
            <div class="profile-image">
                <?php if(empty($user['head_url'])): ?>
                <img src="/static/inspinia/img/profile.jpg" class="img-circle circle-border m-b-md" alt="头像">
                <?php else: ?>
                <img src="<?php echo htmlentities($user['head_url']); ?>" class="img-circle circle-border m-b-md" alt="头像">
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <div class="">
                    <div>
                        <h2 class="no-margins">
                            <?php echo htmlentities($user['nickname']); ?> | UID:<?php echo htmlentities($user['user_id']); if(is_array($user['groups']) || $user['groups'] instanceof \think\Collection || $user['groups'] instanceof \think\Paginator): $i = 0; $__LIST__ = $user['groups'];if( count($__LIST__)==0 ) : echo "未分组" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><span class='label label-primary'><?php echo htmlentities($vo['title']); ?></span><?php endforeach; endif; else: echo "未分组" ;endif; ?>
                        </h2>
                        <h4s><?php echo htmlentities($user['email']); ?></h4s>
                        <small>description</small>
                        <ul class="tag-list" style="padding:0;">
                            <li><a href="#"> VIP <i class="fa <?php if($user['status'] == 2): ?>fa-check text-success<?php else: ?>fa-close text-danger<?php endif; ?>"></i></a></li>
                            <li><a class="ajax-a" data-callback="changeFa" href="<?php echo url('User/onOff',['uid'=>$user['user_id'],'table'=>'user','field'=>'mailactive']); ?>"> 邮件激活 <i class="fa <?php if($user['status'] ==1): ?>fa-check text-success<?php else: ?>fa-close text-danger<?php endif; ?>"></i> </a></li>
                            <li><a class="ajax-a" data-callback="active" href="<?php echo url('User/onOff',['uid'=>$user['user_id'],'table'=>'user','field'=>'active']); ?>"> 激活<i class="fa <?php if($user['status'] == 2): ?>fa-check text-success<?php else: ?>fa-close text-danger<?php endif; ?>"></i></a></li>
                            <li><a class="js-a" data-callback="changeFa" href="<?php echo url('User/onOff',['uid'=>$user['user_id'],'table'=>'user','field'=>'svip']); ?>"> 代理<i class="fa <?php if($user['status'] == 1): ?>fa-check text-success<?php else: ?>fa-close text-danger<?php endif; ?>"></i></a></li>
                        </ul>

                        <div class="btn-group" style="margin-top: 6px;">
                            <a class="btn btn-sm btn-info" href="<?php echo url('User/editUser',['uid'=>$user['user_id']]); ?>"> <i class="fa fa-edit"></i> 修改 </a>
                            <a class="btn btn-sm btn-primary" href="<?php echo url('User/changePwd',['uid'=>$user['user_id']]); ?>"> <i class="fa fa-key"></i> 改密 </a>
                            <?php if($user['status'] == 1 || $user['status'] == 3): ?>
                            <label class="ladda-button btn btn-sm btn-success ajax-btn" data-style="zoom-in" data-action="<?php echo url('User/active',['uid'=>$user['user_id']]); ?>"> <i class="fa fa-check"></i> 激活 </label>
                            <?php elseif($user['status'] == 2): ?>
                            <label class="ladda-button btn btn-sm btn-danger ajax-btn" data-style="zoom-in" data-action="<?php echo url('User/freeze',['uid'=>$user['user_id']]); ?>"> <i class="fa fa-close"></i> 禁用 </label>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-info" href="<?php echo url('User/editUser',['uid'=>$user['user_id']]); ?>"> <i class="fa fa-user-times"></i> 角色管理 </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-12">
            <table class="table small m-b-xs">
                <tbody>
                <tr>
                    <td><strong>注册时间</strong></td>
                    <td><?php echo htmlentities($user['register_time']); ?></td>
                </tr>
                <tr>
                    <td><strong>注册IP</strong></td>
                    <td><?php echo htmlentities(ip_to_address($user['register_ip'], 'province,city')); ?></td>
                </tr>
                <tr>
                    <td><strong>最近登录</strong></td>
                    <td><?php echo htmlentities($user['last_login_time']); ?></td>
                </tr>
                <tr>
                    <td><strong>最近IP</strong></td>
                    <td><?php echo htmlentities(ip_to_address($user['last_login_ip'], 'province,city')); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-5 col-sm-12">
            <table class="table small m-b-xs">
                <tbody>
                <tr>
                    <td><strong>来源</strong></td>
                    <td><?php echo htmlentities($user['from_referee']); ?></td>
                </tr>
                <tr>
                    <td><strong>首访</strong></td>
                    <td><?php echo htmlentities($user['entrance_url']); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 m-b-lg">
            <div id="vertical-timeline" class="vertical-container light-timeline no-margins">

                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon navy-bg">
                        <i class="fa fa-comments"></i>
                    </div>
                    <div class="vertical-timeline-content">
                        <h2>个人资料</h2>
                        <p>昵称: <?php echo htmlentities((isset($user['username']) && ($user['username'] !== '')?$user['username']:"未命名")); ?></p>
                        <p>邮箱: <a href="mailto:<?php echo htmlentities($user['email']); ?>"><?php echo htmlentities($user['email']); ?></a></p>
                        <p>用户分组: <?php if(is_array($user['groups']) || $user['groups'] instanceof \think\Collection || $user['groups'] instanceof \think\Paginator): $i = 0; $__LIST__ = $user['groups'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><label class="label label-default"><?php echo htmlentities($vo['title']); ?></label><?php endforeach; endif; else: echo "" ;endif; ?></p>
                        <p>手机号: <a href="tel:<?php echo htmlentities($user['mobile']); ?>"><?php echo htmlentities((isset($user['mobile']) && ($user['mobile'] !== '')?$user['mobile']:"")); ?></a> </p>
                        <p>QQ: <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo htmlentities($user['qq']); ?>&site=qq&menu=yes"><?php echo htmlentities($user['qq']); ?></a></p>
                        <p>微信: <a href=""><?php echo htmlentities($user['weixin']); ?></a></p>
                        <a href="<?php echo url('User/editUser',['uid'=>$user['user_id']]); ?>" class="btn btn-sm btn-primary"> 修改</a>
                    </div>
                </div>

                <!-- 用户帐户信息 -->
                <?php hook('userbalance', ['user_id'=>$user['user_id']]) ?>

                <!-- 用户状态 -->
                <?php hook('usertimeline', ['user_id'=>$user['user_id']]) ?>

                <!-- 登录日志 -->
                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon lazur-bg">
                        <i class="fa fa-coffee"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>登录日志</h2>
                        <ul class="list-unstyled file-list">
                            <?php if(is_array($actionLogList) || $actionLogList instanceof \think\Collection || $actionLogList instanceof \think\Paginator): $i = 0; $__LIST__ = $actionLogList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <li class="text-success"><?php echo htmlentities($vo['create_time']); ?> <span clsss="label label-success"><?php echo htmlentities($vo['action']); ?></span> - <?php echo htmlentities($vo['module']); ?> - <?php echo htmlentities($vo['remark']); ?></li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>

                        </ul>
                        <a class="btn btn-info btn-sm" href="#">更多</a>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-8">

            <div class="ibox">
                <div class="ibox-content">
                    <h3>VIP服务</h3>
                    <?php if(isset($user['is_vip']) && $user['is_vip'] == true): ?>
                    <p class="text-success"><i class="fa fa-check"></i> VIP用户 期限:<?php echo htmlentities($user['expire_time']); ?> <button class="btn btn-xs btn-warning ajax-btn" data-action="">取消VIP</button></p>
                    <?php else: ?>
                    <p class="text-danger">当前:<i class="fa fa-close"></i> 非VIP用户</p>
                    <?php endif; ?>
                    <form role="form" class="form-inline ajax-form" action="<?php echo url(request()->controller().'/vip'); ?>" method="post">
                        <div class="form-group">
                            <label class="">天数:</label>
                            <input type="number" name="vipDays" min="1" value="1" class="form-control" required="">
                        </div>
                        <input type="hidden" name="uid" value="<?php echo htmlentities($user['user_id']); ?>">
                        <?php if(isset($user['is_vip']) && $user['is_vip'] == true): ?>
                        <button class="btn btn-white" type="submit">延期 VIP</button>
                        <?php else: ?>
                        <button class="btn btn-white" type="submit">升级 VIP</button>
                        <?php endif; ?>
                        <a class="btn btn-info" href="#">操作记录</a>
                    </form>

                </div>
            </div>

            <?php hook('userConfig', ['user_id'=>$user['user_id']]) ?>

            <div class="ibox">
                <div class="ibox-content">
                    <h3>推送消息</h3>
                    <p class="small">
                        给该用户<strong><?php echo htmlentities($user['mobile']); ?></strong>推送消息
                    </p>
                    <form action="<?php echo url(request()->controller() . '/pushMessage'); ?>" class="form ajax-form" method="post">
                        <div class="form-group">
                            <label>标题</label>
                            <input type="text" name="title" class="form-control" placeholder="消息标题" required="">
                        </div>
                        <div class="form-group">
                            <label>内容</label>
                            <textarea class="form-control" name="content" placeholder="消息内容" rows="3" required=""></textarea>
                        </div>
                        <input type="hidden" name="uid" value="<?php echo htmlentities($user['user_id']); ?>">
                        <button class="btn btn-primary btn-block" type="submit">发送</button>
                    </form>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <h3>发送邮件</h3>
                    <p class="small">
                        给该用户<strong><?php echo htmlentities($user['email']); ?></strong>发送邮件
                    </p>
                    <form action="<?php echo url(request()->controller() . '/sendmail'); ?>" class="form ajax-form" method="post" accept-charset="utf-8">
                        <div class="form-group">
                            <label>标题</label>
                            <input type="text" name="title" class="form-control" placeholder="邮件标题" required="">
                        </div>
                        <div class="form-group">
                            <label>内容</label>
                            <textarea class="form-control" name="content" placeholder="邮件内容" rows="3" required=""></textarea>
                        </div>
                        <input type="hidden" name="uid" value="<?php echo htmlentities($user['user_id']); ?>">
                        <button class="btn btn-primary btn-block" type="submit">发送</button>
                    </form>
                </div>
            </div>

            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>我发布的文章 </h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content" style="padding:10px">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover table-condensed">
                            <tr>
                                <th><input type="checkbox" class="ajax-check-all" id="0"></th>
                                <th>ID</th>
                                <th>所属分类</th>
                                <th>标题</th>
                                <th>状态</th>
                                <th>发布时间</th>
                                <th>操作</th>
                            </tr>
                            <?php if(is_array($articleList) || $articleList instanceof \think\Collection || $articleList instanceof \think\Paginator): if( count($articleList)==0 ) : echo "" ;else: foreach($articleList as $key=>$al): ?>
                            <tr>
                                <td><input type="checkbox" class="js-check" id="<?php echo htmlentities($al['id']); ?>"></td>
                                <td><?php echo htmlentities($al['id']); ?></td>
                                <td>
                                    <?php if(is_array($al['categorys']) || $al['categorys'] instanceof \think\Collection || $al['categorys'] instanceof \think\Paginator): $i = 0; $__LIST__ = $al['categorys'];if( count($__LIST__)==0 ) : echo "未分类" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($key > '0'): ?><br><?php endif; ?><span class="label"><?php echo htmlentities($vo['title_cn']); ?></span>
                                    <?php endforeach; endif; else: echo "未分类" ;endif; ?>
                                </td>
                                <td><a href="<?php echo url(request()->controller().'/viewArticle',['id'=>$al['id']]); ?>"><?php echo htmlentities($al['title']); ?></a><?php if($al['is_top'] == '1'): ?><span class="label label-info label-sm">顶</span><?php endif; ?></td>
                                <td><?php echo htmlentities($al['status_text']); ?></td>
                                <td><?php echo htmlentities($al['post_time']); ?></td>
                                <td>
                                    <a href="<?php echo url(request()->controller().'/viewArticle',['id'=>$al['id']]); ?>"><button class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="查看"><i class="fa fa-eye"></i> 查看</button></a>
                                    <a href="<?php echo url(request()->controller().'/editArticle',['id'=>$al['id']]); ?>"><button class="btn btn-xs btn-warning" data-toggle="tooltip" data-placement="top" title="修改"><i class="fa fa-pencil"></i> 修改</button></a>
                                    <?php if($al['status'] == '0'): ?>
                                    <a href="<?php echo url(request()->controller().'/postArticle',['id'=>$al['id']]); ?>" class="ajax-a"><button class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="top" title="发布"><i class="fa fa-upload"></i> 发布</button></a>
                                    <?php endif; if($al['ad_id'] == '0'): ?>
                                    <button class="btn btn-xs btn-success addHeadline" data-title="<?php echo htmlentities($al['title']); ?>" data-url="<?php echo url('cms/Article/viewArticle',['aid'=>$al['id']]); ?>" data-article-id="<?php echo htmlentities($al['id']); ?>" data-ad-id="<?php echo htmlentities((isset($al['ad_id']) && ($al['ad_id'] !== '')?$al['ad_id']:0)); ?>"><i class="fa fa-hand-o-up"></i> 上头条</button>
                                    <?php else: ?>
                                    <button class="btn btn-xs btn-white js-btn" data-action="<?php echo url(request()->controller().'/deleteTop',['adId'=>$al['ad_id'],'artId'=>$al['id']]); ?>"><i class="fa fa-hand-o-down"></i> 取消头条</button>
                                    <?php endif; if($al['is_top'] == '0'): ?>
                                    <button class="btn btn-xs btn-info js-btn" data-action="<?php echo url(request()->controller().'/setTop',['id'=>$al['id']]); ?>"><i class="fa fa-arrow-circle-up"></i> 置顶</button>
                                    <?php else: ?>
                                    <button class="btn btn-xs btn-white js-btn" data-action="<?php echo url(request()->controller().'/unsetTop',['id'=>$al['id']]); ?>"><i class="fa fa-arrow-circle-down"></i> 取消置顶</button>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-danger js-btn-delete" data-action="<?php echo url(request()->controller().'/deleteArticle',['id'=>$al['id']]); ?>"><i class="fa fa-remove"></i> 删除</button>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            <tfoot>
                            <tr>
                                <td colspan="7"><?php echo $articleList->render(); ?></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 用户业务列表01 -->
            <?php hook('userBusiness01', ['user_id'=>$user['user_id']]) ?>
        </div>
    </div>
</div>




  </div>

  <script src="/static/inspinia/js/bootstrap.min.js"></script>

  <script src="/static/inspinia/js/plugins/jquery-ui/jquery-ui.min.js"></script>
  <script src="/static/ueditor/third-party/codemirror/codemirror.js"></script>
  <!-- 通知 -->
  <script src="/static/inspinia/js/plugins/toastr/toastr.min.js"></script>
  <!-- 验证 -->
  <script src="/static/inspinia/js/plugins/validate/jquery.validate.min.js"></script>
  <script src="/static/admin/js/validate_msg_cn.js" type="text/javascript" charset="utf-8" async defer></script>
  <!-- 提示 -->
  <script src="/static/inspinia/js/plugins/sweetalert/sweetalert.min.js"></script>


  <!-- 页面自定义底部js -->
  



  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
