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
 * Class Post
 * @package app\admin\command
 */
class Post extends Command
{
    protected function configure()
    {
        $this->setName('article:post')
            ->addOption('timing', null, Option::VALUE_OPTIONAL, 'The article to timing post', null)
            ->setDescription('Timed publication of articles');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('__article timing post Crontab job start...');
        Log::info('article timing post Crontab job start...');

        $result = \app\admin\job\Article::postTimingArticles();
        $successCount = $result['success_count'];
        $failCount = $result['fail_count'];

        $output->writeln("__article timing post stat:  ($successCount) success, (" . ($failCount) .") error!");
        $output->writeln('__article timing post Crontab job end...');
        Log::info("article timing post stat:  ($successCount) success, (" . ($failCount) .") error!");
        Log::info('article timing post Crontab job end...');
    }
}