<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-07-09
 * Time: 12:55
 */

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\console\input\Option;
use think\facade\Log;

use app\common\model\ArticleMetaModel;
use app\common\model\ArticleModel;

/**
 * Class Article
 * @package app\admin\command
 *  php think article publish #定时发布
 *  php think article index #索引
 *  php think article webmaster --checkIndex #收录检测
 *  php think article webmaster --pushLinks
 */
class Article extends Command
{
    protected function configure()
    {
        $this->setName('article')
            ->addArgument('publish', Argument::OPTIONAL, 'the article to timing publish', null)
            ->addArgument('index', Argument::OPTIONAL, 'use elastic search to index article content', null)
            ->addArgument('webmaster', Argument::OPTIONAL, 'web master tools', null)
            ->addOption('checkIndex', null, Option::VALUE_OPTIONAL, 'check search engine index for article', null)
            ->addOption('id', null, Option::VALUE_OPTIONAL, 'article id value or ids split by \',\'', null)
            ->setDescription('articles manage tools');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!empty($input->getArgument('publish'))) {
            $this->publish($input, $output);
        } else if (!empty($input->getArgument('index'))) {
            $this->index($input, $output);
        } else {
            $this->webmaster($input, $output);
        }
    }

    //文章定时发布
    protected function publish(Input $input, Output $output)
    {
        $ids = $input->getOption('id');
        if (!empty($ids)) {
            $ids = explode(',', $ids);
        }

        $output->writeln('__article publish start...');
        Log::info('article publish start...');

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
            if ($ArticleModel['status'] == ArticleModel::STATUS_PUBLISHED) {
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

        $output->writeln("__article publish stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        $output->writeln('__article publish end...');
        Log::info("article publish stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        Log::info('article publish end...');
    }

    //elastic search入库索引
    protected function index(Input $input, Output $output)
    {
        $output->writeln('__article index start...');
        Log::info('article index start...');


        $output->writeln('__article index end...');
        Log::info('article index end...');
    }

    //webmaster 站长工具
    protected function webmaster(Input $input, Output $output)
    {
        $output->writeln('__article webmaster start...');
        Log::info('article webmaster start...');


        $output->writeln('__article index end...');
        Log::info('article webmaster end...');
    }
}