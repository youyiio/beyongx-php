<?php
namespace app\admin\controller;

use app\common\model\MessageModel;
use think\App;
use think\helper\Time;
use app\common\model\UserModel;
use app\common\model\ArticleModel;

/**
* 首页控制器
*/
class Index extends Base
{

    public function index()
    {
        return view();
    }

    public function welcome()
    {
        $info = array(
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            'PHP运行版本' => PHP_VERSION,
            'ThinkPHP版本' => App::VERSION,
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time').'秒',
            '服务器时间' => date("Y年n月j日 H:i:s"),
            '北京时间' => gmdate("Y年n月j日 H:i:s", time()+8*3600),
            '服务器域名/IP' => $_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间' => round((disk_free_space(".") / (1024*1024)), 2).'M',
            'register_globals' => get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc' => (1===get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1===get_magic_quotes_runtime()) ? 'YES' : 'NO',
        );
        $this->assign('info', $info);

        return $this->fetch("welcome");
    }

    public function dashboard()
    {

        list($todayBeginTime, $todayEndTime) = Time::today();
        list($curWeekBeginTime, $curWeekEndTime) = Time::week();
        list($curMonthBeginTime, $curMonthEndTime) = Time::month();

        list($yesterdayBeginTime, $yesterdayEndTime) = Time::yesterday();
        list($lastWeekBeginTime, $lastWeekEndTime) = Time::lastWeek();
        list($lastMonthBeginTime, $lastMonthEndTime) = Time::lastMonth();

        $where = [];
        $where[] = ['create_time','between', [date_time($todayBeginTime), date_time($todayEndTime)]];
        $yesterdayWhere[] = ['create_time','between', [date_time($yesterdayBeginTime), date_time($yesterdayEndTime)]];

        $ArticleModel = new ArticleModel();
        $todayCount = $ArticleModel->where($where)->count();
        $yesterdayCount = $ArticleModel->where($yesterdayWhere)->count();
        if ($yesterdayCount === 0) { //除数不能为0
            $dayPercent = $todayCount * 100;
        } else {
            $dayPercent = (($todayCount - $yesterdayCount) / $yesterdayCount) * 100;
        }

        unset($where);
        $where[] = ['create_time','between', [date_time($curWeekBeginTime), date_time($curWeekEndTime)]];
        $lastWeekWhere[] = ['create_time','between', [date_time($lastWeekBeginTime), date_time($lastWeekEndTime)]];
        $curWeekCount = $ArticleModel->where($where)->count();
        $lastWeekCount = $ArticleModel->where($lastWeekWhere)->count();
        if ($lastWeekCount === 0) {//除数不能为0
            $weekPercent = $curWeekCount * 100;
        } else {
            $weekPercent = (($curWeekCount - $lastWeekCount) / $lastWeekCount) * 100;
        }

        unset($where);
        $where[] = ['create_time', 'between', [date_time($curMonthBeginTime), date_time($curMonthEndTime)]];
        $lastMonthWhere[] = ['create_time', 'between', [date_time($lastMonthBeginTime), date_time($lastMonthEndTime)]];
        $curMonthCount = $ArticleModel->where($where)->count();
        $lastMonthCount = $ArticleModel->where($lastMonthWhere)->count();
        if ($lastMonthCount === 0) {//除数不能为0
            $monthPercent = $curMonthCount * 100;
        } else {
            $monthPercent = (($curMonthCount - $lastMonthCount) / $lastMonthCount) * 100;
        }

        unset($where);
        $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHING];
        $waitForPublishCount = $ArticleModel->where($where)->count();

        unset($where);
        $where[] = ['status', '>=', ArticleModel::STATUS_PUBLISHING];
        $where[] = ['status', '<', ArticleModel::STATUS_PUBLISHED];
        $waitForAuditCount = $ArticleModel->where($where)->count();

        $this->assign('todayCount', $todayCount);
        $this->assign('curWeekCount', $curWeekCount);
        $this->assign('curMonthCount', $curMonthCount);

        $this->assign('waitForPublishCount', $waitForPublishCount);
        $this->assign('waitForAuditCount', $waitForAuditCount);

        $this->assign('dayPercent', $dayPercent);
        $this->assign('weekPercent', $weekPercent);
        $this->assign('monthPercent', $monthPercent);

        return view();
    }

    public function today()
    {
        $option =[
            'xAxis'=> ['data'=>[]],
            'series'=> [['data'=>[]]],
        ];

        $where = [];
        $UserModel = new UserModel();
        for ($hour = 0; $hour < 24; $hour++) {
            $beginTime = mktime($hour, 0, 0, date('m'), date('d'), date('Y'));
            $endTime = mktime($hour, 59, 59, date('m'), date('d'), date('Y'));

            unset($where);
            $where[] = ['status', '>=', UserModel::STATUS_APPLY];
            $where[] = ['register_time', 'between', [date_time($beginTime), date_time($endTime)]];
            $inquiryCount = $UserModel->where($where)->count();

            array_push($option['xAxis']['data'], $hour.'时');
            array_push($option['series'][0]['data'], $inquiryCount);
        }

        $this->success('success', '', $option);
    }

    public function month()
    {
        $option =[
            'xAxis'=> ['data'=>[]],
            'series'=> [['data'=>[]]],
        ];

        $where = [];
        $UserModel = new UserModel();
        for ($day = 1; $day <= date('t'); $day++) {
            $beginTime = mktime(0, 0, 0, date('m'), $day, date('Y'));
            $endTime = mktime(23, 59, 59, date('m'), $day, date('Y'));

            unset($where);
            $where[] = ['status','>=', UserModel::STATUS_APPLY];
            $where[] = ['register_time','between', [date_time($beginTime), date_time($endTime)]];
            $inquiryCount = $UserModel->where($where)->count();

            array_push($option['xAxis']['data'], $day.'日');
            array_push($option['series'][0]['data'], $inquiryCount);
        }

        $this->success('success', '', $option);
    }

    public function year()
    {
        $option =[
            'xAxis'=> ['data'=>[]],
            'series'=> [['data'=>[]]],
        ];

        $where = [];
        $UserModel = new UserModel();
        for ($month = 1; $month <= 12; $month++) {
            $beginTime = mktime(0, 0, 0, $month, 1, date('Y'));
            $endTime = mktime(23, 59, 59, $month, date("t",strtotime(date('Y') ."-$month")), date('Y'));

            unset($where);
            $where[] = ['status','>=', UserModel::STATUS_APPLY];
            $where[] = ['register_time','between', [date_time($beginTime), date_time($endTime)]];
            $inquiryCount = $UserModel->where($where)->count();

            array_push($option['xAxis']['data'], $month.'月');
            array_push($option['series'][0]['data'], $inquiryCount);
        }

        $this->success('success', '', $option);
    }
}
