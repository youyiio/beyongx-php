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

use app\common\model\ArticleMetaModel;
use app\common\model\ArticleModel;

/**
 * Class Publish
 * @package app\admin\command
 */
class Publish extends Command
{
    protected function configure()
    {
        $this->setName('article:publish')
            ->addOption('timing', null, Option::VALUE_OPTIONAL, 'The article to timing publish', null)
            ->setDescription('Timed publication of articles');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('__article timing post Crontab job start...');
        Log::info('article timing post Crontab job start...');

        //request()->module('admin');//设置当前模块，以使model可用；

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

            $ArticleModel = ArticleModel::get($articleId);
            if ($ArticleModel['status'] == ArticleModel::STATUS_PUBLISHED
                || $ArticleModel['status'] == ArticleModel::STATUS_DELETED) {
                ArticleMetaModel::destroy(['id' => $meta->id]);
                continue;
            }

            $data = [
                'status' => ArticleModel::STATUS_PUBLISHED,
                'post_time' => $meta->meta_value,
            ];

            $result = $ArticleModel->isUpdate(true)->save($data, ['id' => $articleId]);
            if ($result) {
                $count++;
            }

            ArticleMetaModel::destroy(['id' => $meta->id]);
        }

        $output->writeln("__article timing post stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        $output->writeln('__article timing post Crontab job end...');
        Log::info("article timing post stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        Log::info('article timing post Crontab job end...');
    }
}