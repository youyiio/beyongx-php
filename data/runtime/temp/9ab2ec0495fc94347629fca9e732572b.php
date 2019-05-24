<?php /*a:2:{s:70:"D:\server\wnmp\wwwroot\Cms\application\admin\view\resource\images.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
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
<link href="/static/admin/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">


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
          <h5>图片列表 </h5>
          <div class="ibox-tools">
            <a class="collapse-link">
              <i class="fa fa-chevron-up"></i>
            </a>
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="fa fa-wrench"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
              <li><a href="<?php echo url(request()->controller().'/uploadImage'); ?>">上传图片</a></li>
            </ul>
            <a class="close-link">
              <i class="fa fa-times"></i>
            </a>
          </div>
        </div>

        <div class="ibox-content">
          <div class="row">
            <div class="col-lg-4">
              <a class="btn btn-primary " href="<?php echo url(request()->controller().'/uploadImage'); ?>">图片上传</a>
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
            <div class="col-lg-12 ">

              <?php if(is_array($imageList) || $imageList instanceof \think\Collection || $imageList instanceof \think\Paginator): $i = 0; $__LIST__ = $imageList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$image): $mod = ($i % 2 );++$i;?>
              <div class="file-box">
                <div class="file ">
                  <div class="pull-right " id="deleteBtn" style="display: none">
                    <a class="btn-circle ajax-a" href="<?php echo url('Resource/deleteImage',['imageId'=> $image['image_id']]); ?>"><i class="fa fa-times-circle fa-2x "></i></a>
                  </div>
                  <div class="lightBoxGallery">
                    <a href="<?php echo htmlentities($image['full_image_url']); ?>" data-gallery>
                      <!--<span class="corner"></span>-->
                        <img alt="image" class="img-responsive" src="<?php echo htmlentities($image['full_image_url']); ?>" >
                    </a>
                  </div>
                  <div class="file-name">
                      <?php echo htmlentities($image['remark']); ?>
                    <br/>
                    <small>上传时间:<?php echo htmlentities($image['create_time']); ?></small>
                  </div>
                </div>
              </div>
              <?php endforeach; endif; else: echo "" ;endif; ?>

              <!-- The Gallery as lightbox dialog, should be a child element of the document body -->
              <div id="blueimp-gallery" class="blueimp-gallery ">
                <div class="slides"></div>
                <a class="close">×</a>

              </div>

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
<!-- blueimp gallery -->
<script src="/static/inspinia/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
<script>

  //删除图片
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
