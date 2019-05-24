<?php /*a:2:{s:65:"D:\server\wnmp\wwwroot\Cms\application\admin\view\user\index.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
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
  
<!-- ladda   -->
<link href="/static/admin/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">


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
                <h5>用户列表 </h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-wrench"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="<?php echo url('User/addUser'); ?>">新增用户</a></li>
                    </ul>
                    <a class="close-link">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content" style="padding:10px">
                <div class="row">
                    <div class="col-lg-12  col-md-12 m-b-xs">
                        <div class="btn-group">
                            <a href="<?php echo url('User/index'); ?>"  class="btn btn-sm <?php if(input('status','')===''): ?>btn-default active<?php else: ?>btn-white<?php endif; ?>">所有用户<span class="badge"><?php echo htmlentities($userTotal); ?></span></a>
                        </div>
                        <div class="btn-group">
                            <a href="<?php echo url('User/index',['status'=>\app\common\model\UserModel::STATUS_FREEZED]); ?>" class=" btn btn-sm <?php if(input('status','')==3): ?>btn-warning active<?php else: ?>btn-white<?php endif; ?>">冻结用户<span class="badge badge-danger"><?php echo htmlentities($freezeTotal); ?></span></a>
                        </div>
                        <div class="btn-group">
                            <a href="<?php echo url('User/index',['status'=>\app\common\model\UserModel::STATUS_ACTIVED]); ?>" class=" btn btn-sm <?php if(input('status','')==2): ?>btn-info active<?php else: ?>btn-white<?php endif; ?>">激活用户<span class="badge badge-success"><?php echo htmlentities($activeTotal); ?></span></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-4 m-b-xs">
                        <div class="btn-group">
                            <a href="<?php echo url('User/addUser'); ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i>
                                新增</a>
                        </div>
                        <div data-toggle="buttons" class="btn-group">
                            <label class="ladda-button btn btn-sm btn-danger ajax-batch-set" data-style="zoom-in"
                                   data-action="<?php echo url('User/freeze'); ?>"> <i class="fa fa-close"></i> 禁用 </label>
                        </div>
                        <div data-toggle="buttons" class="btn-group">
                            <label class="ladda-button btn btn-sm btn-primary ajax-batch-set" data-style="zoom-in"
                                   data-action="<?php echo url('User/active'); ?>"> <i class="fa fa-check"></i> 激活 </label>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 text-right">
                        <form method="get" role="form" class="form-inline">
                            <div class="form-group">
                                <input type="text" name="key" placeholder="关键字" class="form-control"
                                       value="<?php echo htmlentities((app('request')->get('key') ?: '')); ?>">
                            </div>
                            <div class="form-group">
                                <select name="type" class="form-control">
                                    <option value="mobile" <?php if(!(empty(app('request')->get('type')) || ((app('request')->get('type') instanceof \think\Collection || app('request')->get('type') instanceof \think\Paginator ) && app('request')->get('type')->isEmpty()))): if(app('request')->get('type') == 'mobile'): ?>selected<?php endif; ?><?php endif; ?>>手机号</option>
                                    <option value="email" <?php if(!(empty(app('request')->get('type')) || ((app('request')->get('type') instanceof \think\Collection || app('request')->get('type') instanceof \think\Paginator ) && app('request')->get('type')->isEmpty()))): if(app('request')->get('type') == 'email'): ?>selected<?php endif; ?><?php endif; ?>>邮箱</option>
                                    <option value="user_id" <?php if(!(empty(app('request')->get('type')) || ((app('request')->get('type') instanceof \think\Collection || app('request')->get('type') instanceof \think\Paginator ) && app('request')->get('type')->isEmpty()))): if(app('request')->get('type') == 'user_id'): ?>selected<?php endif; ?><?php endif; ?>>UID</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0px">查找</button>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-border">
                        <thead>
                        <tr>
                            <th><input type="checkbox" class="i-checks ajax-check-all"></th>
                            <th>用户ID</th>
                            <th>手机</th>
                            <th>邮箱</th>
                            <th>昵称</th>
                            <th>用户分组</th>
                            <th>注册时间</th>
                            <th>最后登录</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty())): ?>
                        <tr>
                            <td colspan="8">暂无数据</td>
                        </tr>
                        <?php else: if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input type="checkbox" class="i-checks ajax-check" value="<?php echo htmlentities($vo['user_id']); ?>"></td>
                            <td><?php echo htmlentities($vo['user_id']); ?></td>
                            <td><?php echo htmlentities($vo['mobile']); ?></td>
                            <td><?php echo htmlentities($vo['email']); ?></td>
                            <td><?php echo htmlentities($vo['nickname']); ?></td>
                            <td>
                                <?php if(!(empty($vo['groups']) || (($vo['groups'] instanceof \think\Collection || $vo['groups'] instanceof \think\Paginator ) && $vo['groups']->isEmpty()))): if(is_array($vo['groups']) || $vo['groups'] instanceof \think\Collection || $vo['groups'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['groups'];if( count($__LIST__)==0 ) : echo "未分组" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                <span class="label label-info"><?php echo htmlentities($v['title']); ?></span>
                                <?php endforeach; endif; else: echo "未分组" ;endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlentities($vo['register_time']); ?></td>
                            <td><?php echo htmlentities($vo['last_login_time']); ?></td>
                            <td><?php echo $vo['status_html']; ?></td>
                            <td>
                                <a href="<?php echo url(request()->module().'/User/viewUser',['uid'=>$vo['user_id']]); ?>"
                                   class="btn btn-xs btn-info">详情</a>
                                <a href="<?php echo url('User/editUser',['uid'=>$vo['user_id']]); ?>"
                                   class="btn btn-xs btn-warning">修改</a>
                                <button class="btn btn-xs btn-success changePwd" data-uid="<?php echo htmlentities($vo['user_id']); ?>">修改密码</button>
                                <button class="btn btn-xs btn-danger ajax-btn-warning"
                                        data-action="<?php echo url('User/deleteUser',['uid'=>$vo['user_id']]); ?>"
                                        data-title="确定删除此用户吗？" data-text="">删除
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <?php endif; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="10" class="footable-visible"><?php echo $pages; ?></td>
                        </tr>
                        </tfoot>
                    </table>
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
  
<!-- Ladda -->
<script src="/static/inspinia/js/plugins/ladda/spin.min.js"></script>
<script src="/static/inspinia/js/plugins/ladda/ladda.min.js"></script>
<script src="/static/inspinia/js/plugins/ladda/ladda.jquery.min.js"></script>
<script type="text/javascript">
    //批量任务操作 审核,取消审核,删除
    var l = $('.ajax-batch-set').ladda();
    l.click(function () {
        // Start loading
        $(this).ladda('start');

        var uids = getAjaxCheckedValues(); //选择的任务
        var action = $(this).data('action');
        $.each(uids, function (i, uid) {
            $.post(action, {uid: uid}, function (data) {
                //console.log(data);
                if (data.code == 0 && uids.length == 1) {
                    layer.msg(data.msg, function(){});
                    location.reload();
                    return false;
                }
            });
        });

        setTimeout(function () {
            location.reload();
        }, 800);
    });

    //修改密码
    $('.changePwd').click(function (e) {
        e.preventDefault();
        var uid = $(this).data('uid');
        swal({
            title: '修改密码',
            text: '请输入新的密码',
            type: 'input',
            showCancelButton: true,
            closeOnConfirm: false,
            disableButtonsOnConfirm: true,
            confirmLoadingButtonColor: '#DD6B55',
            inputPlaceholder: "请输入新的密码"
        }, function (inputValue) {
            if (inputValue === false) return false;
            if (inputValue === "") {
                swal.showInputError("You need to write something!");
                return false
            }
            $.post("<?php echo url('User/changePwd'); ?>", {'uid': uid, 'newPwd': inputValue}, function (res) {
                if (res.code) {
                    $.post("<?php echo url('User/sendMail'); ?>", {
                        'uid': uid,
                        'title': '密码变更',
                        'message': '管理员修改了您的密码,您的新密码为:' + inputValue
                    });
                    swal(res.msg, 'success');
                } else {
                    swal(res.msg, 'error');
                }
            })
        });
    });
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
