<?php /*a:2:{s:78:"D:\server\wnmp\wwwroot\Cms\application\admin\view\resource\uploadDocument.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | <?php echo htmlentities((isset($columnTitle) && ($columnTitle !== '')?$columnTitle:"文档管理")); ?></title>

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
  
<link href="/static/admin/css/plugins/datapicker/bootstrap-datetimepicker.min.css" rel="stylesheet">
<link href="/static/admin/css/plugins/dropzone/dropzone-4.3.0.min.css" rel="stylesheet">
<link href="/static/admin/css/plugins/chosen/chosen.css" rel="stylesheet">


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    

<div class="wrapper wrapper-content animated fadeInRight">
  <div class="ibox float-e-margins">
    <div class="ibox-title">
      <h5>文档上传</h5>
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
      <form class="form-horizontal ajax-form" enctype="multipart/form-data"  method="post">

        <div class="form-group">
          <label class="col-lg-2 control-label" >上传文件</label>
          <div class="col-lg-10">
            <input type="hidden" name="fileId" id="fileId" value="">

            <div class="dropzone needsclick dz-clickable" id="uploadFile" data-input-name="fileId"  >
              <div class="dz-message needsclick">

                点击此处上传文档 <br>
                <span class="note needsclick"></span>

              </div>
              <div class="text fileName"></div>
            </div>
            <span class="help-block m-b-none">文件类型:doc,docx,ppt,pptx,txt,avi,pdf,mp3,zip,mp4,xlsx; &nbsp;&nbsp;&nbsp;&nbsp;   文件小于200M</span>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label">备注</label>
          <div class="col-lg-10">
            <textarea class="form-control" name="remark" placeholder="请填写备注"></textarea>
            <span class="help-block m-b-none"></span>
          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-offset-2 col-lg-10">
            <button class="btn btn-primary btn-lg" type="submit" id="submitBtn">上传</button>
          </div>
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
  
<!-- 日期 -->
<script src="/static/inspinia/js/plugins/datapicker/bootstrap-datetimepicker.min.js"></script>
<script src="/static/inspinia/js/plugins/datapicker/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<!-- 上传插件 -->
<script src="/static/inspinia/js/plugins/dropzone/dropzone-4.3.0.min.js"></script>

<!-- Chosen -->
<script src="/static/inspinia/js/plugins/chosen/chosen.jquery.js"></script>

<!-- 引入ueditor的js代码 -->
<script src="/static/ueditor/ueditor.config.js?2017032702"></script>
<script src="/static/ueditor/ueditor.all.js?2017041701"></script>
<script>

    var config = {
        '.chosen-select'           : {},
        '.chosen-select-deselect'  : {allow_single_deselect:true},
        '.chosen-select-search'    : {search_contains:true},
        '.chosen-select-no-single' : {disable_search_threshold:10},
        '.chosen-select-no-results': {no_results_text:'没有匹配的选项'},
        '.chosen-select-width'     : {width:"100%!important"}
    };
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }


    //图片上传
    Dropzone.autoDiscover = false;
    $("#uploadFile").dropzone({
        // autoProcessQueue:false,
        url: "<?php echo url('File/upload'); ?>",
        paramName: 'file', //input的name
        maxFilesize: 200, // MB
        addRemoveLinks: true, //添加删除链接
        maxFiles: 1, //最多上传文件数量
        acceptedFiles: '.doc,.docx,.ppt,.pptx,.txt,.avi,.pdf,.mp3,.zip,.mp4,.xlsx', //允许上传的文件后缀
        dictCancelUpload: '取消上传',
        dictRemoveFile: '删除图片',
        maxfilesexceeded: function(file) {
            swal('超最大数量,请删除现有文件后再上传');
            file.previewElement.remove();
        },
        sending: function(file,xhr,formData) {
            var $element = $(this.element);

        },
        success: function(file,response) {
            //admin.log(response);
            var $element = $(this.element);

            if (response.code) {
                //将图片id填入input
                var fileId = response.data.file_id;
                var fileName = "" + response.data.file_name;
                //console.log(fileName);
                $('#' + $element.data('inputName')).val(fileId);
                $(this).find('.note').text(fileName);
                // var displayHtml = '<img src="'+imageUrl+'" style="border:1px solid #ccc;width:_thumbWidth_px;height:_thumbHeight_px;max-width:100%">';

                // file.previewElement.remove();
                // this.removeFile(file);
            } else {
                swal('上传失败', response.msg, 'error');
                file.previewElement.remove();
            }
        },
        complete: function(file){

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
