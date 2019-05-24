<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 11:14
 */

namespace app\admin\command;


use app\common\model\ArticleMetaModel;
use app\common\model\ArticleModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class ArticlePublish extends Command
{
    protected function configure()
    {
        $this->setName('timing')->setDescription('Timed publication of articles');
    }


    protected function execute(Input $input, Output $output)
    {
        $output->writeln('article timing post Crontab job start...');
        Log::info('article timing post Crontab job start...');

        request()->module('admin');//设置当前模块，以使model可用；

        $currentTime = date_time();
        $where = [
            ['meta_key', '=', ArticleMetaModel::KEY_TIMING_POST],
            ['meta_value', '<=', $currentTime]
        ];

        $ArticleMetaModel = new ArticleMetaModel();
        $metas = $ArticleMetaModel->where($where)->select();
        //print_r($metas);

        $totalCount = count($metas);
        $count = 0;

        foreach ($metas as $meta) {
            $articleId = $meta->article_id;
            $data = [
                'status' => ArticleModel::STATUS_PUBLISHED,
                'post_time' => $meta->meta_value,
            ];

            $ArticleModel = ArticleModel::get($articleId);
            $result = $ArticleModel->isUpdate(true)->save($data, ['id' => $articleId]);
            if ($result) {
                $count++;
            }

            ArticleMetaModel::destroy(['id' => $meta->id]);
        }

        $output->writeln("article timing post stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        $output->writeln('article timing post Crontab job end...');
        Log::info("article timing post stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        Log::info('article timing post Crontab job end...');
    }
}