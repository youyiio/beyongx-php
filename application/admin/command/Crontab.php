<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-08-29
 * Time: 9:42
 */

namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Config;
use think\facade\Log;
use think\Queue;

/**
 * Class Crontab
 * 定时任务入口
 * 运行 start_timer.bat 或 sh start_timer.sh
 * 会将此命令配置至window执行计划或Linux crontab任务计划
 * @package app\admin\command
 */
class Crontab extends Command
{
    //   # Use the hash sign to prefix a comment
    //   # +—————- minute (0 – 59)
    //   # | +————- hour (0 – 23)
    //   # | | +———- day of month (1 – 31)
    //   # | | | +——- month (1 – 12)
    //   # | | | | +—- day of week (0 – 7) (Sunday=0 or 7)
    //   # | | | | |
    //   # * * * * * command to be executed
    protected $jobs = [
        //格式：job_name, [crontab定时时间, data参数]
        'app\admin\job\Article@timingPost' => ['*/1 * * * *', []], //定时发布文章，每分钟检测
    ];

    protected function configure()
    {
        $this->setName('crontab')
            ->addOption('period', null, Option::VALUE_OPTIONAL, 'cron period time', null)
            ->setDescription('Unify Crontab command of beyongx');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('beyongx Crontab  start...');
        Log::info('beyongx Crontab  start..');

        //与配置合并
        $jobs = array_merge($this->jobs, Config::pull('crontab'));
        foreach ($jobs as $jobName => $job) {
            $runnable = false;
            try {
                $cron = $job[0];
                $runnable = $this->isCronRunnable($cron);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                Log::error($e->getMessage());
            }

            if (!$runnable) {
                continue;
            }

            $jobHandlerClass = $jobName;
            //任务的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串; jobData 为对象时，存储其public属性的键值对
            $jobData = $job[1];
            $jobData['create_time'] = date_time();
            //任务归属的队列名称，如果为新队列，会自动创建
            $jobQueue = config('queue.default');

            $isPushed = Queue::push($jobHandlerClass, $jobData, $jobQueue);
            // database 驱动时，返回值为 1|false; redis 驱动时，返回值为 随机字符串|false
            if ($isPushed !== false) {
                $output->writeln("通过Crontab 启动 job => $jobHandlerClass 成功..");
                Log::info("通过Crontab 启动 job => $jobHandlerClass 成功..");
            } else {
                $output->writeln("通过Crontab 启动 job => $jobHandlerClass 失败..");
                Log::info("通过Crontab 启动 job => $jobHandlerClass 失败..");
            }
        }

        $output->writeln('beyongx Crontab  end...');
        Log::info('beyongx Crontab  end..');
    }


    /**
     * 检测当前时间是否可运行cron
     * @param $cronString
     * @param null $currentTimestamp
     * @return bool
     */
    protected function isCronRunnable($cronString, $currentTimestamp = null)
    {
        $regExp = '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i';
        if (!preg_match($regExp, trim($cronString))) {
            throw new \InvalidArgumentException("Invalid cron string: " . $cronString);
        }
        if ($currentTimestamp && !is_numeric($currentTimestamp)) {
            throw new \InvalidArgumentException("\$currentTimestamp must be a valid unix timestamp ($currentTimestamp given)");
        }

        $cron = preg_split("/[\s]+/i", trim($cronString));
        $currentTimestamp = empty($currentTimestamp) ? time() : $currentTimestamp;
        $date = array(
            'minutes' => $this->parseCronNumbers($cron[0], 0, 59),
            'hours' => $this->parseCronNumbers($cron[1], 0, 23),
            'day' => $this->parseCronNumbers($cron[2], 1, 31),
            'month' => $this->parseCronNumbers($cron[3], 1, 12),
            'week' => $this->parseCronNumbers($cron[4], 0, 6),
        );

        if (in_array(intval(date('j', $currentTimestamp)), $date['day']) &&
            in_array(intval(date('n', $currentTimestamp)), $date['month']) &&
            in_array(intval(date('w', $currentTimestamp)), $date['week']) &&
            in_array(intval(date('G', $currentTimestamp)), $date['hours']) &&
            in_array(intval(date('i', $currentTimestamp)), $date['minutes'])) {
            return true;
        }

        return false;
    }

    /**
     * get a single cron style notation and parse it into numeric value
     * 解析元素设置，返回可选的值集合
     * @param string $s cron string element
     * @param int $min minimum possible value
     * @param int $max maximum possible value
     * @return array parsed number
     */
    protected function parseCronNumbers($s, $min, $max)
    {
        $result = array();
        $v = explode(',', $s);
        foreach ($v as $vv) {
            $vvv = explode('/', $vv);

            $step = empty($vvv[1]) ? 1 : $vvv[1];
            $vvvv = explode('-', $vvv[0]);
            $_min = count($vvvv) == 2 ? $vvvv[0] : ($vvv[0] == '*' ? $min : $vvv[0]);
            $_max = count($vvvv) == 2 ? $vvvv[1] : ($vvv[0] == '*' ? $max : $vvv[0]);
            for ($i = $_min; $i <= $_max; $i += $step) {
                $result[$i] = intval($i);
            }
        }

        ksort($result);
        return $result;
    }

}
