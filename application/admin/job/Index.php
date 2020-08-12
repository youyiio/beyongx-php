<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-08-30
 * Time: 10:22
 */

namespace app\admin\job;

use think\console\command\Help;
use think\facade\Log;
use think\helper\Time;
use think\queue\Job;

use app\common\model\ArticleModel;
use app\common\model\ArticleMetaModel;

/**
 * 搜索引擎收录业务逻辑及job入口
 * Class Index
 * @package app\admin\job
 */
class Index
{

    public function check(Job $job, $data)
    {
        Log::info("job[{$data['create_time']}] 检测今日文章索引 Job开始...");

        $range = $data['range'];
        if (empty($range)) {
            $job->delete();
            return;
        }

        if ($range == 'today') {
            $today = Time::today();
            $range = [
                date_time($today[0], 'Y-m-d'),
                date_time($today[1], 'Y-m-d')
            ];
            self::withRange($range);
        }

        $job->delete();
        Log::info('检测今日文章索引已完成');
    }

    //*****************静态业务逻辑，供Job及command调用**********************
    public static function withId($aid)
    {
        $article = ArticleModel::find($aid);
        if (!$article) {
            Log::info("文章: $aid 未找到");
            return false;
        }

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章: $aid 状态未发布");
            return false;
        }

        $url = url('article/' . $aid, [], true, get_config('domain_name')); //job中使用url，获取异常

        $indexed = Webmaster::baiduCheckIndex($url);
        if ($indexed) {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 1);
        } else {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 0);
        }

        Log::info('check index: ' . $url . ', result: ' . ($indexed ? 'true' : 'false'));

        return true;
    }

    //@param $range, 文章id区间，如[1, 99]或时间区间，['2019-01-07','2019-04-07']
    public static function withRange($range)
    {
        $range = array_slice($range, 0, 2);

        $where = [
            ['status', '=', ArticleModel::STATUS_PUBLISHED]
        ];
        if (is_numeric($range[0]) && is_numeric($range[1])) {
            Log::info('range 是 数字区间');
            $where[] = ['id', 'between', $range];
        } else {
            Log::info('range 是 时间区间');
            $range[0] = date_time(strtotime($range[0]));
            $range[1] = date_time(strtotime($range[1] . ' 23:59:59'));
            $where[] = ['post_time', 'between', $range];
        }

        $list = ArticleModel::where($where)->field('id')->order('id desc')->select();
        foreach ($list as $model) {
            self::withId($model['id']);
        }
    }

    //所有发布文章
    public static function withAll($all)
    {
        $where = [
            ['status', '=', ArticleModel::STATUS_PUBLISHED]
        ];

        $list = ArticleModel::where($where)->field('id')->order('id desc')->select();
        foreach ($list as $model) {
            self::withId($model['id']);
        }
    }
}