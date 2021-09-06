<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2019-08-14
 * Time: 11:30
 */

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Log;

/**
 * 搜索引擎收录检测
 * 用法：
 * >php think article:index --aid=100001  | php think article:index --aid 100001
 * >php think article:index --range 100000 --range 150000  (--range 2019-08-01 --range 2019-08-10)
 * >php think article:index --all
 * @package app\admin\command
 */
class Index extends Command
{
    protected function configure()
    {
        $this->setName('article:index')
            ->addOption('aid', 'id', Option::VALUE_REQUIRED, 'The article to timing publish', 0)
            ->addOption('range', 'r', Option::VALUE_REQUIRED | Option::VALUE_IS_ARRAY, 'The article to timing publish', [])
            ->addOption('all', 'a', Option::VALUE_OPTIONAL, 'The article to timing publish', 'all')
            ->setDescription('Check search engine index status');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('__article search engine index start...');
        Log::info('article search engine index start...');

        //dump($input->getOptions());
        if ($input->getOption('aid')) {
            $aid = $input->getOption('aid');
            \app\admin\job\Index::withId($aid);
        } else if ($input->getOption('range')) {
            $range = $input->getOption('range');
            \app\admin\job\Index::withRange($range);
        } else if ($input->getOption('all')) {
            $all = $input->getOption('all');
            \app\admin\job\Index::withAll($all);
        } else {
            $output->warning(' do nothing ....');
        }

        $output->writeln('__article search engine index end...');
        Log::info('article search engine index end...');
    }
}