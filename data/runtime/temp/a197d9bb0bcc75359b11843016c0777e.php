<?php /*a:2:{s:69:"D:\server\wnmp\wwwroot\Cms\application\admin\view\feedback\index.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 用户列表</title>

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
  
<link href="/static/admin/css/plugins/codemirror/codemirror.css" rel="stylesheet">
<link href="/static/admin/css/plugins/codemirror/ambiance.css" rel="stylesheet">


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg 
    full-height-layout
">

  <div class="wrapper wrapper-content">
    

<div class="fh-breadcrumb">

    <div class="fh-column">

        <div class="full-height-scroll " >
            <ul class="list-group elements-list">
                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$fb): $mod = ($i % 2 );++$i;?>
                <li class="list-group-item ">
                    <a href="javascript:void(0)"  data-send_client_id="<?php echo htmlentities($fb['send_client_id']); ?>" data-feedback_id="<?php echo htmlentities($fb['feedback_id']); ?>">
                        <small class="pull-right text-muted"><?php echo htmlentities($fb['create_time']); ?></small>
                        <strong><?php echo htmlentities($fb['sender']); ?></strong>
                        <div class="small m-t-xs">
                            <p><?php echo htmlentities($fb['content']); ?></p>

                            <?php if($fb['status'] <= $sendStatus): ?>
                            <!--<p class="pull-right"><i class="fa fa-circle text-danger"></i></p>-->
                            <span class="label pull-right label-primary"><?php echo htmlentities($fb['count']); ?>条未读</span>
                            <?php endif; ?>

                            <p class="m-b-none"><i class="fa fa-map-marker"></i>来自<?php echo htmlentities($fb['ip']); ?></p>
                        </div>
                    </a>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
    <!--消息窗口-->
    <div class="full-height" >
        <div class="full-height-scroll white-bg border-left">

            <div class="element-detail-box">

                <div class="tab-content ">

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
  

<!-- Mainly scripts -->
<!-- Custom and plugin javascript -->
<script src="/static/inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/static/inspinia/js/plugins/datapicker/bootstrap-datetimepicker.min.js"></script>
<script src="/static/inspinia/js/plugins/datapicker/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript">
    $(function () {

        //消息窗内容的加载
        $('li.list-group-item >a').click(function () {

            var sendClientId = $(this).attr("data-send_client_id");
            var feedbackId = $(this).attr("data-feedback_id");
            var upData = $(this).data();
            // admin.log(upData);
            $('.tab-content').load('<?php echo url("chat"); ?>', {send_client_id:sendClientId, feedback_id: feedbackId}, function (response,status) {

            })
        });

        //li>a点击 添加bg-primary类

        $(' li.list-group-item >a').click(function (e) {

           var $liGroup = $(this).parents('ul.list-group').children('li.list-group-item ');

           $liGroup.each(function () {
               $(this).children('a').removeClass('bg-info');
           });

           $(this).addClass('bg-info');

        });

    })
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
