<?php /*a:2:{s:67:"D:\server\wnmp\wwwroot\Cms\application\admin\view\person\index.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 个人首页</title>

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
    

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="row">
    <div class="col-lg-4 m-b-lg">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>我的资料 </h5>
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

          <p>昵称: <?php echo htmlentities((isset($user['nickname']) && ($user['nickname'] !== '')?$user['nickname']:"未命名")); ?></p>
          <p>邮箱: <a href="mailto:<?php echo htmlentities($user['email']); ?>"><?php echo htmlentities($user['email']); ?></a></p>
          <p>用户分组: <?php foreach($user['groups'] as $g): ?><span class="label label-primary"><?php echo htmlentities($g['title']); ?></span><?php endforeach; ?></p>
          <p>手机号: <a href="tel:<?php echo htmlentities($user['mobile']); ?>"><?php echo htmlentities((isset($user['mobile']) && ($user['mobile'] !== '')?$user['mobile']:"")); ?></a> </p>

          <p>注册时间: <?php echo htmlentities($user['register_time']); ?></p>
          <p>最后登录: <?php echo htmlentities($user['last_login_time']); ?></p>
          <p>最后登录IP: <?php echo htmlentities($user['last_login_ip']); ?></p>
          <a href="<?php echo url('Person/profile'); ?>" class="btn btn-sm btn-primary"> 修改资料</a>
          <a href="<?php echo url('Person/password'); ?>" class="btn btn-sm btn-warning"> 修改密码</a>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
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
                <th><input type="checkbox" class="js-check-all" id="0"></th>
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
                <td><a href="<?php echo url(request()->module() . '/Article/viewArticle',['id'=>$al['id']]); ?>"><?php echo htmlentities($al['title']); ?></a><?php if($al['is_top'] == '1'): ?><span class="label label-info label-sm">顶</span><?php endif; ?></td>
                <td><?php echo htmlentities($al['status_text']); ?></td>
                <td><?php echo htmlentities($al['post_time']); ?></td>
                <td>
                  <a href="<?php echo url(request()->module().'/Article/viewArticle',['id'=>$al['id']]); ?>"><button class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="查看"><i class="fa fa-eye"></i> 查看</button></a>
                  <a href="<?php echo url(request()->module().'/Article/editArticle',['id'=>$al['id']]); ?>"><button class="btn btn-xs btn-warning" data-toggle="tooltip" data-placement="top" title="修改"><i class="fa fa-pencil"></i> 修改</button></a>
                  <?php if($al['status'] == '0'): ?>
                  <a href="<?php echo url(request()->module().'/Article/postArticle',['id'=>$al['id']]); ?>" class="ajax-a"><button class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="top" title="发布"><i class="fa fa-upload"></i> 发布</button></a>
                  <?php endif; if($al['ad_id'] == '0'): ?>
                  <button class="btn btn-xs btn-success addHeadline" data-title="<?php echo htmlentities($al['title']); ?>" data-url="<?php echo url('cms/Article/viewArticle',['aid'=>$al['id']]); ?>" data-article-id="<?php echo htmlentities($al['id']); ?>" data-ad-id="<?php echo htmlentities((isset($al['ad_id']) && ($al['ad_id'] !== '')?$al['ad_id']:0)); ?>"><i class="fa fa-hand-o-up"></i> 上头条</button>
                  <?php else: ?>
                  <button class="btn btn-xs btn-white js-btn" data-action="<?php echo url(request()->controller().'/deleteTop',['adId'=>$al['ad_id'],'artId'=>$al['id']]); ?>"><i class="fa fa-hand-o-down"></i> 取消头条</button>
                  <?php endif; if($al['is_top'] == '0'): ?>
                  <button class="btn btn-xs btn-info ajax-btn" data-action="<?php echo url(request()->module().'/Article/setTop',['id'=>$al['id']]); ?>"><i class="fa fa-arrow-circle-up"></i> 置顶</button>
                  <?php else: ?>
                  <button class="btn btn-xs btn-white ajax-btn" data-action="<?php echo url(request()->module().'/Article/unsetTop',['id'=>$al['id']]); ?>"><i class="fa fa-arrow-circle-down"></i> 取消置顶</button>
                  <?php endif; ?>
                  <button class="btn btn-xs btn-danger ajax-btn-warning" data-action="<?php echo url(request()->module().'/Article/deleteArticle',['id'=>$al['id']]); ?>"><i class="fa fa-remove"></i> 删除</button>
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
    </div>
  </div>
</div>
<div class="modal inmodal" id="addAd" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <i class="fa fa-laptop modal-icon"></i>
        <h4 class="modal-title">添加头条</h4>
        <small class="font-bold">图片大小 1002*375</small>
      </div>
      <form class="ajax-form" action="<?php echo url(request()->controller().'/upTop'); ?>" method="post">
        <div class="modal-body">
          <div class="form-group"><label>标题</label> <input type="text" name="title" required="" placeholder="标题" class="form-control" value=""></div>
          <div class="form-group"><label>链接</label> <input type="text" name="url" required="" placeholder="链接" class="form-control" value="#" readonly=""></div>
          <div class="form-group">
            <label>上传图片</label>
            <input type="hidden" name="image_id" id="image_id" value="<?php echo htmlentities((isset($info['image_id']) && ($info['image_id'] !== '')?$info['image_id']:'')); ?>">
            <div class="dropzone needsclick dz-clickable" data-img-width="1002" data-img-height="375" data-tb-width="720" data-tb-height="361" data-input-name="image_id">
              <div class="dz-message needsclick">
                点击此处上传广告图 宽1002 高375<br>
                <span class="note needsclick"></span>

              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="artId">
          <button type="submit" class="btn btn-primary">提交</button>
        </div>
      </form>
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
    $('.addHeadline').click(function(e){
        e.preventDefault();
        var $adModal = $('#addAd');
        var _this = $(this);
        $adModal.find('input[name="title"]').val(_this.data('title'));
        $adModal.find('input[name="url"]').val(_this.data('url'));
        $adModal.find('input[name="artId"]').val(_this.data('articleId'));
        $adModal.modal('show');
    });
    //图片上传
    Dropzone.autoDiscover = false;
    $(".dropzone").dropzone({
        url: "<?php echo url('Image/upload'); ?>",
        paramName: 'Filedata', //input的name
        maxFilesize: 5, // MB
        addRemoveLinks: true, //添加删除链接
        clickable: true, //预览图可点击
        maxFiles: 1, //最多上传文件数量
        acceptedFiles: '.jpg,.gif,.png,.jpeg', //允许上传的文件后缀
        // thumbnailWidth: 320,
        // thumbnailheight: 200,
        dictCancelUpload: '取消上传',
        dictRemoveFile: '删除图片',
        maxfilesexceeded: function(file) {
            swal('超最大数量,请删除现有文件后再上传');
            file.previewElement.remove();
        },
        sending: function(file,xhr,formData) {
            var $element = $(this.element);
            //图片尺寸
            formData.append("imgWidth", $element.data('imgWidth'));
            formData.append("imgHeight", $element.data('imgHeight'));
            //缩略图尺寸
            formData.append("tbWidth", $element.data('tbWidth'));
            formData.append("tbHeight", $element.data('tbHeight'));
        },
        success: function(file,response) {
            admin.log(response);
            if (response.code) {
                //将图片id填入input
                var imageId = response.data.image_id;
                var imageUrl = ""+response.data.image_url;
                $('#image_id').val(imageId);
                $('.dz-message').html('').append('<img src="'+imageUrl+'" style="border:1px solid #ccc;width:1002px;height:375px;max-width:100%">');
                // file.previewElement.remove();
                this.removeFile(file);
            } else {
                swal('上传失败',response.msg,'error');
                file.previewElement.remove();
            }
        },
        complete: function(file){
            admin.log(this.options.maxFiles);
            if (this.options.maxFiles == 1) {
                this.removeFile(file);
            }
        },
        error: function(file,message) {
            //上传错误
            swal('上传失败',message,'error');
            file.previewElement.remove();
        }
    });
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
