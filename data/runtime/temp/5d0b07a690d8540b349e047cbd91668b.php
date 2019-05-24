<?php /*a:2:{s:67:"D:\server\wnmp\wwwroot\Cms\application\admin\view\user\addUser.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 新增用户</title>

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
  
<link href="/static/admin/css/plugins/chosen/chosen.css" rel="stylesheet">


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5>新增用户</h5>
        <div class="ibox-tools">
          <a class="collapse-link">
            <i class="fa fa-chevron-up"></i>
          </a>
          <a class="close-link">
            <i class="fa fa-times"></i>
          </a>
        </div>
      </div>
      <div class="ibox-content">
        <form class="form-horizontal ajax-form" action="<?php echo url('User/addUser'); ?>" method="post">
          <input type="password" style="display:none"><!-- for disable autocomplete on chrome -->
          <div class="form-group">
            <label class="col-lg-2 control-label">用户分组</label>
            <div class="col-lg-10">
              <select name="group_ids[]" tabindex="4" required="" data-placeholder="选择分组" class="form-control chosen-select form-control chosen-select-deselect chosen-select-no-single chosen-select-no-results chosen-select-search" multiple style="width:100%!important;">
                <?php if(is_array($groups) || $groups instanceof \think\Collection || $groups instanceof \think\Paginator): $i = 0; $__LIST__ = $groups;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <option value="<?php echo htmlentities($vo['id']); ?>"><?php echo htmlentities($vo['title']); ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
              <span class="help-block m-b-none">普通用户可以不选分组</span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-2 control-label">昵称</label>
            <div class="col-lg-10"><input type="text" name="nickname" placeholder="昵称" class="form-control" autocomplete="off"> <span class="help-block m-b-none"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-2 control-label">手机号</label>
            <div class="col-lg-10"><input type="text" name="mobile" placeholder="11位手机号" class="form-control" autocomplete="off" maxlength="11"> <span class="help-block m-b-none"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-2 control-label">邮箱</label>
            <div class="col-lg-10"><input type="email" name="email" placeholder="邮箱" class="form-control"> <span class="help-block m-b-none"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-2 control-label">登录密码</label>
            <div class="col-lg-10"><input type="password" name="password" placeholder="登录密码" class="form-control" autocomplete="off"> <span class="help-block m-b-none"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-2 control-label">确认密码</label>
            <div class="col-lg-10">
              <input type="password" name="repassword" placeholder="确认密码" class="form-control" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
              <input type="hidden" name="status" value="1">
              <button class="btn btn-sm btn-white" type="submit">提交</button>
            </div>
          </div>
        </form>
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
  
<!-- Chosen -->
<script src="/static/inspinia/js/plugins/chosen/chosen.jquery.js"></script>

<script>
    var config = {
        '.chosen-select': {},
        '.chosen-select-deselect': {allow_single_deselect: true},
        '.chosen-select-search': {search_contains: true},
        '.chosen-select-no-single': {disable_search_threshold: 10},
        '.chosen-select-no-results': {no_results_text: '没有匹配的选项'},
        '.chosen-select-width': {width: "100%!important"}
    }
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
