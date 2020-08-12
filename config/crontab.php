<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-09-03
 * Time: 10:43
 */

return [
    //格式：crontab定时时间， [job_name, data参数]
    'app\admin\job\Article@timingPost' => ['*/1 * * * *', []], //定时发布文章，每分钟检测
    //'app\admin\job\Index@check' => ['0 */1 * * *', ['range' => 'today']], //检测当天的收录情况,每1小时
    //'app\admin\job\Crawler@timingCrawl' => ['0 */1 * * *', []], //检测定时采集,每1小时
];