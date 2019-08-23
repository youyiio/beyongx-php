<?php
/**
 * Created by PhpStorm.
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

use app\admin\job\Webmaster;

use app\common\model\ArticleModel;
use app\common\model\ArticleMetaModel;

/**
 * 搜索引擎收录检测
 * 用法：
 * >php think article:index --aid=100001  | php think article:index --aid 100001
 * >php think article:index --range=100000 --range=150000  (--range=2019-08-01 --range=2019-08-10)
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
            ->setDescription('search engine index');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('__article search engine index start...');
        Log::info('article search engine index start...');

        //dump($input->getOptions());
        if ($input->getOption('aid')) {
            $this->withId($input, $output, $input->getOption('aid'));
        } else if ($input->getOption('range')) {
            $this->withRange($input, $output, $input->getOption('range'));
        } else if ($input->getOption('all')) {
            $this->withAll($input, $output, $input->getOption('all'));
        } else {
            $output->warning(' do nothing ....');
        }

        $output->writeln('__article search engine index end...');
        Log::info('article search engine index end...');
    }

    protected function withId(Input $input, Output $output, $aid)
    {
        $article = ArticleModel::find($aid);
        if (!$article) {
            Log::info("文章: $aid 未找到");
            return;
        }

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章: $aid 状态未发布");
            return;
        }

        $webmaster = new Webmaster();

        $url = url('article/' . $aid, [], true, get_config('domain_name')); //job中使用url，获取异常

        $indexed = $webmaster->baiduCheckIndex($url);
        if ($indexed) {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 1);
        } else {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 0);
        }

        $output->writeln('check index: ' . $url . ', result: ' . ($indexed ? 'true' : 'false'));
        Log::info('check index: ' . $url . ', result: ' . ($indexed ? 'true' : 'false'));
    }

    protected function withRange(Input $input, Output $output, $range)
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
            $range[1] = date_time(strtotime($range[1]));
            $where[] = ['post_time', 'between', $range];
        }

        $list = ArticleModel::where($where)->field('id')->order('id desc')->select();
        foreach ($list as $model) {
            $this->withId($input, $output,$model['id']);
        }
    }

    protected function withAll(Input $input, Output $output, $all)
    {
        $where = [
            ['status', '=', ArticleModel::STATUS_PUBLISHED]
        ];

        $list = ArticleModel::where($where)->field('id')->order('id desc')->select();
        foreach ($list as $model) {
            $this->withId($input, $output,$model['id']);
        }
    }
}