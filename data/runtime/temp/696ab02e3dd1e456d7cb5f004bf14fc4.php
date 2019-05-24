<?php /*a:2:{s:68:"D:\server\wnmp\wwwroot\Cms\application\admin\view\user\userStat.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="renderer" content="webkit">

  <title><?php echo get_config('site_name'); ?> | 新增用户查询</title>

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
<link rel="stylesheet" href="/static/admin/css/plugins/datapicker/bootstrap-datetimepicker.min.css">


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
                <h5>用户统计 </h5>
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
                <div class="col-lg-4 col-md-4 m-b-xs ">
                    <div class="form-group">
                        <?php if(empty($count) || (($count instanceof \think\Collection || $count instanceof \think\Paginator ) && $count->isEmpty())): ?>
                        <label for="queryDate">
                            <span class="btn btn-warning">请输入需要查询的时间</span>
                        </label>
                        <?php else: ?>
                        <button class="btn btn-default btn-sm">用户注册量<span class="badge badge-info"><?php echo htmlentities($count); ?></span></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-8 col-md-8 text-right">
                    <form method="get" role="form" class="form-inline">
                        <div class="form-group">
                            <input type="text" class="form-datetime form-control" placeholder="起始日期" id="queryDate" name="startTime" value="<?php echo htmlentities((app('request')->get('startTime') ?: $startTime)); ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-datetime form-control" placeholder="终止日期" name="endTime" value="<?php echo htmlentities((app('request')->get('endTime') ?: $endTime)); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-bottom: 0px">查找</button>
                    </form>
                </div>

                <div class="row col-lg-12">


                    <div class="pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-success btn-tab"
                                    data-action="<?php echo url('user/echartShow',['start'=>$startTimestamp,'end'=>$endTimestamp]); ?>">
                                报表
                            </button>
                        </div>

                    </div>

                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="flot-chart">
                            <div class="flot-chart-content" id="flot-usercount-chart"></div>
                        </div>

                    </div>
                    <div class="col-lg-12">

                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-border">
                                <thead>
                                <tr>
                                    <!--<th><input type="checkbox" class="i-checks js-check-all"></th>-->
                                    <th>用户ID</th>
                                    <th>手机</th>
                                    <th>邮箱</th>
                                    <th>昵称</th>
                                    <th>注册时间</th>
                                    <th>状态</th>
                                    <th>推荐人</th>
                                    <th>来源</th>
                                    <th>首访</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty())): ?>
                                <tr>
                                    <td colspan="8">暂无数据</td>
                                </tr>
                                <?php else: if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td><?php echo htmlentities($vo['user_id']); ?></td>
                                    <td><?php echo htmlentities($vo['mobile']); ?></td>
                                    <td><?php echo htmlentities($vo['email']); ?></td>
                                    <td><?php echo htmlentities($vo['nickname']); ?></td>
                                    <td><?php echo htmlentities($vo['register_time']); ?></td>
                                    <td><?php echo $vo['status_html']; ?></td>
                                    <td><?php echo htmlentities($vo['referee']); ?></td>
                                    <td><span title="<?php echo htmlentities($vo['from_referee']); ?>"><?php echo htmlentities(sub_str($vo['from_referee'],0,30)); ?></span></td>
                                    <td><span title="<?php echo htmlentities($vo['entrance_url']); ?>"><?php echo htmlentities(sub_str($vo['entrance_url'],0,30)); ?></span></td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                <?php endif; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="9" class="footable-visible"><?php echo $pages; ?></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
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
  
<!-- Ladda -->
<script src="/static/inspinia/js/plugins/ladda/spin.min.js"></script>
<script src="/static/inspinia/js/plugins/ladda/ladda.min.js"></script>
<script src="/static/inspinia/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/static/inspinia/js/plugins/datapicker/bootstrap-datetimepicker.min.js"></script>
<script src="/static/inspinia/js/plugins/datapicker/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<!-- echarts -->
<script src="/static/admin/js/echarts.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    animation: false,
                    label: {
                        backgroundColor: '#ccc',
                        borderColor: '#aaa',
                        borderWidth: 1,
                        shadowBlur: 0,
                        shadowOffsetX: 0,
                        shadowOffsetY: 0,
                        textStyle: {
                            color: '#222'
                        }
                    }
                },
                formatter: function (params) {
                    return params[0].name + params[0].seriesName + ': ' + params[0].value;
                }
            },
            grid: {
                top:20,
                right:20,
                bottom:40,
                left:40,
            },
            xAxis: {
                type: 'category',
                boundaryGap: true,
                data: []
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '注册量',
                data: [],
                type: 'bar',
                barMaxWidth:60
            }]
        };

        var myChart = echarts.init($('#flot-usercount-chart').get(0), 'light');
        myChart.setOption(option);

        //自适应宽度
        window.addEventListener("resize", function () {
            myChart.resize();
        });


        $(".btn-tab").click(function (e) {
            $(".btn-tab").removeClass('active');
            $(this).addClass('active');

            var url = $(this).data('action');
            myChart.showLoading();
            $.getJSON(url, [1522281600,1523404800], function (json) {
                myChart.hideLoading();
                // admin.log(json);
                if (json.code != 1) {
                    return;
                }

                myChart.setOption(json.data);
            });
        });
        $(".btn-tab:first").trigger('click');
    });

    //日期控件
    $('.form-datetime').datetimepicker({
        language: 'zh-CN',
        format: 'yyyy-mm-dd',
        startView: 'month',
        minView: 2,
        autoclose: true,
        todayBtn: true
    });




    $('#dateType').change(function () {
        if ($('#dateType').val() == "day")
        {
            $('.form-datetime').datetimepicker({
                language: 'zh-CN',
                format: 'yyyy-mm-dd',
                startView: 'month',
                minView: 2,
                autoclose: true,
                todayBtn: true
            });
        } else {
            $('.form-datetime').datetimepicker({
                language: 'zh-CN',
                format: 'yyyy-mm',
                startView: 'year',
                minView: 3,
                autoclose: true,
                todayBtn: false
            });
        }
    })



</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
