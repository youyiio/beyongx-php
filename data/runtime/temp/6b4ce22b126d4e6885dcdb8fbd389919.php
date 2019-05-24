<?php /*a:2:{s:74:"D:\server\wnmp\wwwroot\Cms\application\admin\view\article\viewArticle.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | <?php echo htmlentities((isset($columnTitle) && ($columnTitle !== '')?$columnTitle:"文章管理")); ?></title>

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
    

<div class="wrapper wrapper-content  animated fadeInRight article">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox-content" style="padding:10px">
                <div class="ibox">
                    <a class="btn btn-sm btn-primary" href="<?php echo url('article/articleStat',['id'=>$id]); ?>">文章访问用户统计</a>
                    <div class="pull-right">
                        <?php if(is_array($info['categorys']) || $info['categorys'] instanceof \think\Collection || $info['categorys'] instanceof \think\Paginator): $i = 0; $__LIST__ = $info['categorys'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <span class="label"><?php echo htmlentities($vo['title_cn']); ?></span>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <button class="btn btn-white btn-xs" id="btnCopy" data-clipboard-text="<?php echo url('cms/Article/viewArticle', ['aid'=>$info['id']], true, get_config('domain_name')); ?>">
                            复制文章地址
                        </button>
                    </div>
                    <div class="text-center article-title">
                        <h1>
                            <?php echo htmlentities($info['title']); ?>
                        </h1>
                        <?php if($info['status'] == 5): ?>
                        <span class="text-muted"> 创建时间<?php echo htmlentities($info['create_time']); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;发布时间：<?php echo htmlentities($info['last_update_time']); ?></span>
                        <?php else: ?>
                        <span class="text-muted"> 创建时间<?php echo htmlentities($info['create_time']); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;发布时间：未发布</span>
                        <?php endif; ?>
                    </div>
                    <?php echo $info['content']; ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="small text-right">
                                <h5>Stats:<?php echo htmlentities($info['status_text']); ?></h5>
                                <i class="fa fa-eye"> </i> <?php echo htmlentities((isset($info['read_count']) && ($info['read_count'] !== '')?$info['read_count']:0)); ?> views
                            </div>
                        </div>
                    </div>

                    <?php if(empty($comments) || (($comments instanceof \think\Collection || $comments instanceof \think\Paginator ) && $comments->isEmpty())): ?>
                    <h2>该篇文章未有人评论</h2>
                    <?php else: ?>
                    <h2>评论：</h2>
                    <?php if(is_array($comments) || $comments instanceof \think\Collection || $comments instanceof \think\Paginator): $i = 0; $__LIST__ = $comments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$comment): $mod = ($i % 2 );++$i;?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="social-feed-box">
                                <div class="social-avatar">
                                    <a href="#" class="pull-left">
                                        <img alt="image" src="/static/theme/wenews/images/header_img1.png">
                                    </a>
                                    <div class="media-body">
                                        <a href="#">
                                            <?php echo htmlentities($comment['author']); ?>
                                        </a>
                                        <small class="text-muted"><?php echo htmlentities(date("Y-m-d H:i",!is_numeric($comment['create_time'])? strtotime($comment['create_time']) : $comment['create_time'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="social-body">
                                    <p>
                                        <?php echo htmlentities($comment['content']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <div style="text-align: center; padding:30px 0">
                        <?php echo $comments->render(); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
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
  
<script src="/static/inspinia/js/plugins/clipboard/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('#btnCopy');
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
