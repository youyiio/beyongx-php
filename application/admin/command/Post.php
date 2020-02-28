<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 11:14
 */

namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Log;

/**
 * 文章发布
 * 用法：
 * >php think article:post 等同于 发布已定时的文章
 * >php think article:post --timing  发布已定时的文章【默认行为】
 * >php think article:post --range 10000-30000
 * @package app\admin\command
 */
class Post extends Command
{
    protected function configure()
    {
        $this->setName('article:post')
            ->addOption('timing', null, Option::VALUE_OPTIONAL, 'post the timing articles', null)
            ->addOption('range', null, Option::VALUE_REQUIRED, 'post the ids range articles', null)
            ->setDescription('Post the articles, by --timing or --range');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('__article post Crontab job start...');
        Log::info('article post Crontab job start...');

        //dump($input->getOptions());
        if (!empty($input->getOption('timing'))) {
            $this->postTimingArticles($input, $output);
        } else if (!empty($input->getOption('range'))) {
            $this->postRangeArticles($input, $output);
        } else {
            $this->postTimingArticles($input, $output);
        }

        $output->writeln('__article post Crontab job end...');
        Log::info('article post Crontab job end...');
    }

    /**  文章定时发布 **/
    protected function postTimingArticles(Input $input, Output $output)
    {
        $output->writeln('timing post start...');
        Log::info('timing post start...');

        $result = \app\admin\job\Article::postTimingArticles();
        $successCount = $result['success_count'];
        $failCount = $result['fail_count'];

        $output->writeln("__article timing post stat:  ($successCount) success, (" . ($failCount) .") error!");
        Log::info("article timing post stat:  ($successCount) success, (" . ($failCount) .") error!");

        $output->writeln('timing post end...');
        Log::info('timing post end...');
    }

    /** 发布区间文章  **/
    protected function postRangeArticles(Input $input, Output $output)
    {
        $output->writeln('range post start...');
        Log::info('range post start...');

        $params = $input->getOption('range');
        if (empty($params)) {
            return;
        }

        list($startId, $endId) = explode('-', $params);


        $result = \app\admin\job\Article::postRangeArticles($startId, $endId);
        $successCount = $result['success_count'];
        $failCount = $result['fail_count'];

        $output->writeln("__article range post stat:  ($successCount) success, (" . ($failCount) .") error!");
        Log::info("article range post stat:  ($successCount) success, (" . ($failCount) .") error!");

        $output->writeln('range post end...');
        Log::info('range post end...');
    }
}