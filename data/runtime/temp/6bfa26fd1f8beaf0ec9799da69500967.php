<?php /*a:2:{s:73:"D:\server\wnmp\wwwroot\Cms\application\admin\view\resource\documents.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | <?php echo htmlentities((isset($columnTitle) && ($columnTitle !== '')?$columnTitle:"资源管理")); ?></title>

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


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    

  <div class="wrapper wrapper-content">
    <div class="row">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>文档列表 </h5>
          <div class="ibox-tools">
            <a class="collapse-link">
              <i class="fa fa-chevron-up"></i>
            </a>
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="fa fa-wrench"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
              <li><a href="<?php echo url(request()->controller().'/uploadDocument'); ?>">上传文档</a></li>
            </ul>
            <a class="close-link">
              <i class="fa fa-times"></i>
            </a>
          </div>
        </div>
       <div class="ibox-content" >
         <div class="row">
           <div class="col-lg-4 text-left">
             <a class="btn btn-primary " href="<?php echo url(request()->controller().'/uploadDocument'); ?>">文档上传</a>
           </div>
           <div class="col-lg-8 col-md-8 text-right">
             <form method="get" role="form" class="form-inline">
               <div class="form-group">
                 <input type="text" name="key" placeholder="备注词" class="form-control"
                        value="<?php echo htmlentities((app('request')->get('key') ?: '')); ?>">
               </div>
               <button type="submit" class="btn btn-primary" style="margin-bottom: 0px">查找</button>
             </form>
           </div>
         </div>


        <div class="row">
          <div class="col-lg-12">
            <?php if(is_array($files) || $files instanceof \think\Collection || $files instanceof \think\Paginator): $i = 0; $__LIST__ = $files;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$file): $mod = ($i % 2 );++$i;?>
            <div class="file-box">
              <div class="file">
                <div class="pull-right " id="deleteBtn" style="display: none">
                  <a class="btn-circle ajax-a" href="<?php echo url('Resource/deleteDocument',['fileId'=> $file['file_id']]); ?>"><i class="fa fa-times-circle fa-2x "></i></a>
                </div>

                  <span class="corner"></span>

                  <div class="icon">
                    <?php if($file['type'] == 'doc' || $file['type'] == 'docx'): ?>
                    <i class="fa fa-file-word-o"></i>
                    <?php elseif($file['type'] == 'avi' || $file['type'] == 'mp4'): ?>
                    <i class="fa fa-file-movie-o"></i>
                    <?php elseif($file['type'] == 'ppt' || $file['type'] == 'pptx'): ?>
                    <i class="fa fa-paper-plane"></i>
                    <?php elseif($file['type'] == 'pdf'): ?>
                    <i class="fa fa-file-pdf-o"></i>
                    <?php elseif($file['type'] == 'zip'): ?>
                    <i class="fa fa-file-archive-o"></i>
                    <?php elseif($file['type'] == 'xlsx'): ?>
                    <i class="fa fa-bar-chart-o"></i>
                    <?php else: ?>
                    <i class="fa fa-file"></i>
                    <?php endif; ?>
                  </div>
                  <div class="file-name">
                    <a href="<?php echo htmlentities($file['file_url']); ?>"><?php echo htmlentities($file['file_name']); ?></a>
                    <br/>
                    <small><?php echo htmlentities($file['remark']); ?></small>
                  </div>

              </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>

          </div>
        </div>
         <div class="row">
           <div class="col-lg-12">
             <div class="pull-right" ><?php echo $pages; ?></div>
           </div>
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
  
<!-- 上传插件 -->
<script src="/static/inspinia/js/plugins/dropzone/dropzone-4.3.0.min.js"></script>
<script>
  //删除按钮
  $('.file-box').hover(function (){
          $(this).find('#deleteBtn').show();
      },function () {
          $(this).find('#deleteBtn').hide();
      }
  );

</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
