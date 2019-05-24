<?php /*a:2:{s:68:"D:\server\wnmp\wwwroot\Cms\application\admin\view\index\welcome.html";i:1556245321;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 欢迎页面</title>

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
  
<link href="/static/admin/css/plugins/dropzone/dropzone-4.3.0.min.css" rel="stylesheet">
<link href="/static/admin/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/static/admin/css/bootstrap.min.css" rel="stylesheet" >


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content text-center p-md">

                    <h2 class="font-bold"><?php echo get_config('site_name'); ?></h2>
                    <div class="error-desc">
                        欢迎进入<?php echo get_config('site_name'); ?>管理后台
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-content text-center p-md">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th colspan="6" style="text-align: center;">系统信息</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($info) || $info instanceof \think\Collection || $info instanceof \think\Paginator): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td colspan="3"><?php echo htmlentities($key); ?>：</td>
                            <td colspan="3"><?php echo htmlentities($v); ?></td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--预留给插件使用-->
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-content text-center p-md">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th colspan="6" style="text-align: center; size: A3">官方动态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6">CMS系统更新版本至xx.xx.x</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--预留给插件使用-->

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
