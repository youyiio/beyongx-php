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



}