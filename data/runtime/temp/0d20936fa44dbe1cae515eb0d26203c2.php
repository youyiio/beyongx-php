<?php /*a:2:{s:65:"D:\server\wnmp\wwwroot\Cms\application\admin\view\sign\login.html";i:1556245321;s:64:"D:\server\wnmp\wwwroot\Cms\application\admin\view\sign\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html>

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?>后台 | 登录</title>

  <link href="/static/admin/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/admin/css/font-awesome.min93e3.css" rel="stylesheet">

  <!-- Toastr style -->
  <link href="/static/admin/css/plugins/toastr/toastr.min.css" rel="stylesheet">

  <link href="/static/admin/css/animate.css" rel="stylesheet">
  <link href="/static/admin/css/style.css" rel="stylesheet">

  

</head>

<body class="gray-bg">

  <div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
      <div>

        <!-- <h1 class="logo-name">AdSdk</h1> -->

      </div>
      
<h3><?php echo get_config('site_name'); ?>管理后台</h3>
<p>请用email及您的密码登录后台管理系统</p>
<form class="m-t ajax-form" role="form" action="<?php echo url('admin/Sign/login'); ?>" method="post" data-error-callback="changeCode">
    <div class="form-group">
        <input type="text" class="form-control" name="username" placeholder="邮箱" value="<?php echo htmlentities((app('cookie')->get('username') ?: '')); ?>" required>
    </div>
    <div class="form-group">
        <input type="password" class="form-control" name="password" placeholder="密码" value="<?php echo htmlentities((app('cookie')->get('password') ?: '')); ?>" required autocomplete="off">
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="code" autocomplete="off" placeholder="验证码" required="">
        <div><img id="code" src="<?php echo url(request()->module() . '/Sign/captcha'); ?>" alt="captcha" onclick="this.src='<?php echo url(request()->module() . '/Sign/captcha'); ?>'+ '?' + new Date().getTime()" style="cursor:pointer;margin-top:6px"></div>
    </div>
    <button type="submit" class="btn btn-primary block full-width m-b">登录</button>

    <a href="<?php echo url('admin/Sign/forget'); ?>"><small>忘记密码?</small></a>
    <!--
    <a class="btn btn-sm btn-white btn-block" href="<?php echo url('Login/register'); ?>">注册</a>
    -->
</form>

      <p class="m-t"> <small><?php echo get_config('site_name'); ?> &copy; 2015</small> </p>
    </div>
  </div>

  <!-- jquery-2.1.4 -->
  <script src="/static/inspinia/js/jquery-2.1.1.js"></script>

  <!-- Mainly scripts -->
  <script src="/static/inspinia/js/bootstrap.min.js"></script>

  <!-- Custom and plugin javascript -->
  <!-- <script src="/static/inspinia/js/inspinia.js"></script> -->

  <!-- 通知 -->
  <script src="/static/inspinia/js/plugins/toastr/toastr.min.js"></script>
  <!-- 验证 -->
  <script src="/static/inspinia/js/plugins/validate/jquery.validate.min.js"></script>
  <script src="/static/admin/js/validate_msg_cn.js" type="text/javascript" charset="utf-8" async defer></script>



  <!-- 页面自定义底部js -->
  
<script type="text/javascript">
    if (window != top) {
        top.location.href = location.href;
    }
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>

</html>
