<?php /*a:2:{s:70:"D:\server\wnmp\wwwroot\Cms\application\admin\view\index\dashboard.html";i:1556273981;s:66:"D:\server\wnmp\wwwroot\Cms\application\admin\view\public\base.html";i:1556273981;}*/ ?>
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


  <link href="/static/admin/css/animate.min.css" rel="stylesheet">
  <link href="/static/admin/css/style.min862f.css?v=4.1.0" rel="stylesheet">
  <script src="/static/inspinia/js/jquery.min.js"></script>
</head>

<body class="gray-bg ">

  <div class="wrapper wrapper-content">
    
<div class="row">
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-primary pull-right">今日</span>
                <h5>文章</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins"><?php echo htmlentities($todayCount); ?></h1>
                <div class="stat-percent font-bold text-navy">44% <i class="fa fa-level-up"></i></div>
                <small>新增单数</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-info pull-right">本周</span>
                <h5>文章</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins"><?php echo htmlentities($curWeekCount); ?></h1>
                <div class="stat-percent font-bold text-info">20% <i class="fa fa-level-up"></i></div>
                <small>新增单数</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-success pull-right">本月</span>
                <h5>文章</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins"><?php echo htmlentities($curMonthCount); ?></h1>
                <div class="stat-percent font-bold text-success">98% <i class="fa fa-bolt"></i></div>
                <small>新增单数</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>待发布文章数</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins"><?php echo htmlentities($waitForPublishCount); ?></h1>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>待审核文章数</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins"><?php echo htmlentities($waitForAuditCount); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>用户新增统计</h5>
                <div class="pull-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-white btn-tab" data-action="<?php echo url('index/today'); ?>">今天</button>
                        <button type="button" class="btn btn-xs btn-white btn-tab" data-action="<?php echo url('index/month'); ?>">本月</button>
                        <button type="button" class="btn btn-xs btn-white btn-tab" data-action="<?php echo url('index/year'); ?>">年度</button>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="flot-chart">
                            <div class="flot-chart-content" id="flot-dashboard-chart"></div>
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
  
<!-- echarts -->
<script src="/static/inspinia/js/plugins/echarts/echarts.min.js" type="text/javascript"></script>
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
                boundaryGap: false,
                data: []
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '新增',
                data: [],
                type: 'line',
                lineStyle: {
                    color: 'rgba(26,179,148, 1)'
                },
            }]
        };

        var myChart = echarts.init($('#flot-dashboard-chart').get(0), 'light');
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
            $.getJSON(url, [], function (json) {
                myChart.hideLoading();
                if (json.code != 1) {
                    return;
                }

                myChart.setOption(json.data);
            });
        });
        $(".btn-tab:first").trigger('click');
    });
</script>


  <!-- 自定义基础js -->
  <script src="/static/layui/layui.js"></script>
  <script src="/static/admin/js/app_base.js" type="text/javascript"></script>

</body>
</html>
