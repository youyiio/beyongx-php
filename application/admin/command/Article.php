<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-07-09
 * Time: 12:55
 */

namespace app\admin\command;

use app\admin\job\Webmaster;
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
            ->addArgument('action', Argument::REQUIRED, 'action for the article, as follows: publish, es, webmaster', null)
            //->addArgument('publish', Argument::REQUIRED, 'the article to timing publish', null)
           // ->addArgument('es', Argument::OPTIONAL, 'use elastic search to index article content', null)
            //->addArgument('webmaster', Argument::OPTIONAL, 'web master tools', null)
            ->addOption('checkIndex', null, Option::VALUE_OPTIONAL, 'check search engine index for article', null)
            ->addOption('id', null, Option::VALUE_OPTIONAL, 'article id value or ids split by \',\'', 'all')
            ->setDescription('articles manage tools');

    }

    protected function execute(Input $input, Output $output)
    {
        //dump($input->getArguments());
        dump($input->getOptions());
        $args = $input->getArguments();
        if (empty($args['action'])) {
            $output->error('Action for the article, as follows: publish, es, webmaster');
            return;
        }
        if (!in_array($args['action'], ['publish', 'es', 'webmaster'])) {
            $output->warning('Action for the article, as follows: publish, es, webmaster');
            return;
        }

        if (!empty($input->getArgument('action'))) {
            $this->publish($input, $output);
        } else if (!empty($input->getArgument('es'))) {
            $this->es($input, $output);
        } else if (!empty($input->getArgument('webmaster'))) {
            $this->webmaster($input, $output);
        } else {
            dump($this->getUsages());
        }
    }

    /**  文章定时发布 **/
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

        $output->writeln("__article publish stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        $output->writeln('__article publish end...');
        Log::info("article publish stat:  ($totalCount) success, (" . ($totalCount - $count) .") error!");
        Log::info('article publish end...');
    }

    //elastic search入库索引
    protected function es(Input $input, Output $output)
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

        $webmaster = new Webmaster();
        $webmaster->getTargetUrl('seoaizhan.com', 'bd', 'https://www.baidu.com/s?wd=http://www.seoaizhan.com/article/117874.html');

        $output->writeln('__article index end...');
        Log::info('article webmaster end...');
    }
}