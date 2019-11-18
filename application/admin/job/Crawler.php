<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-03-17
 * Time: 17:33
 */

namespace app\admin\job;

use app\common\model\CrawlerMetaModel;
use think\facade\Log;
use think\queue\Job;
use think\Queue;

use QL\QueryList;

use app\common\model\CrawlerModel;
use app\common\model\ArticleModel;

//采集抓取任务
class Crawler
{

    /**
     * 采集文章网址，并存入cms_crawler_meta表，待文章抓取
     * @param Job $job
     * @param $data
     * @throws \Exception
     */
    public function startCrawl(Job $job, $data)
    {
        Log::info('采集文章网址Job开始...');

        $id = $data['id'];
        if (empty($id)) {
            $job->delete();
            return;
        }

        $crawler = CrawlerModel::get($id);
        if (!$crawler) {
            Log::info('采集规则不存在!');
            $job->delete();
            return;
        }

        $url = $crawler['url'];
        $articleUrl = $crawler['article_url'];
        $isPaging = $crawler['is_paging'];
        $startPage = $crawler['start_page'];
        $endPage = $crawler['end_page'];
        $pagingUrl = $crawler['paging_url'];

        $urls = Crawler::crawlUrls($url, $articleUrl, $isPaging, $startPage, $endPage, $pagingUrl, $id);
        if (empty($urls)) {
            Log::info('未采集到文章网址!');
            $job->delete();
            return;
        }

        $job->delete();
        Log::info('采集文章网址Job结束###');

        //文章网址入库，存储至meta
        $metas = [];
        foreach ($urls as $metaValue) {
            $item = [];
            $item['target_id'] = $id;
            $item['meta_key'] = 'article_url';
            $item['remark'] = CrawlerMetaModel::STATUS_WAREHOUSING;
            $item['meta_value'] = $metaValue;

            //已经抓取过的，不再入库；避免重复抓取
            $temp = CrawlerMetaModel::where(['meta_key' => 'article_url', 'meta_value' => $metaValue])->find();
            if ($temp && $temp['remark'] == CrawlerMetaModel::STATUS_COMPLETE) {
                continue;
            }

            $metas[] = $item;
        }
        $metaCount = count($metas);
        if ($metaCount == 0) {
            Log::info($metaCount . '篇文章网址已入库');

            CrawlerModel::update(['status' => CrawlerModel::STATUS_CRAWL_SUCCESS], ['id' => $id]);
            return;
        }

        $CrawlerMetaModel = new CrawlerMetaModel();
        $CrawlerMetaModel->saveAll($metas);


        Log::info($metaCount . '篇文章网址已入库');

        $uid = $data['uid'];
        //发送消息，进行文章内容采集；
        $jobHandlerClass  = 'app\admin\job\Crawler@crawlArticles';
        $jobData = ['id' => $id, 'uid' => $uid, 'create_time' => date_time()];
        $jobQueue = config('queue.default');
        $isPushed = Queue::push($jobHandlerClass, $jobData, $jobQueue);
    }

    /**
     * 抓取文章明细
     * @param Job $job
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function crawlArticles(Job $job, $data)
    {
        Log::info('采集文章Job开始...');

        $id = $data['id'];
        if (empty($id)) {
            $job->delete();
            return;
        }

        $crawler = CrawlerModel::get($id);
        if (!$crawler) {
            Log::info('采集规则不存在!');
            $job->delete();
            return;
        }
        if ($crawler['status'] == CrawlerModel::STATUS_CRAWL_SUCCESS) {
            Log::info('采集规则已采集完成!');
            $job->delete();
            return;
        }

        $CrawlerMetaModel = new CrawlerMetaModel();

        $where = [
            ['target_id', '=', $id],
            ['meta_key', '=', 'article_url'],
            ['remark', '=', CrawlerMetaModel::STATUS_WAREHOUSING]
        ];
        $numRows = $CrawlerMetaModel->where($where)->count('id');
        if ($numRows <= 0) {
            Log::info("采集规则id: $id 没有需要采集的文章了");
            CrawlerModel::update(['status' => CrawlerModel::STATUS_CRAWL_SUCCESS], ['id' => $id]);

            $job->delete();
            return;
        }

        $uid = $data['uid'];
        $categoryId = $crawler['category_id'];

        $encoding = $crawler['encoding'];
        $articleTitle = $crawler['article_title'];
        $articleDescription = $crawler['article_description'];
        $articleKeywords = $crawler['article_keywords'];
        $articleContent = $crawler['article_content'];
        $articleAuthor = $crawler['article_author'];
        $articleImage = $crawler['article_image'];

        $resultSet = $CrawlerMetaModel->where($where)->limit(2)->select();
        $articles = [];
        foreach ($resultSet as $meta) {
            //开始采集
            $CrawlerMetaModel::update(['remark' => CrawlerMetaModel::STATUS_PENDING], ['id' => $meta['id']]);

            $url = $meta['meta_value'];
            $article = Crawler::crawlArticle($url, $encoding, $articleTitle, $articleDescription, $articleKeywords, $articleContent, $articleAuthor, $articleImage);
            if ($article) {
                $articles[] = $article;

                //采集成功时，更新状态
                $CrawlerMetaModel::update(['remark' => CrawlerMetaModel::STATUS_COMPLETE], ['id' => $meta['id']]);
            } else {
                //采集失败时，更新状态
                $CrawlerMetaModel::update(['remark' => CrawlerMetaModel::STATUS_FAIL], ['id' => $meta['id']]);
            }
        }

        Log::info('采集文章Job结束###');
//        if (count($articles) <= 0) {
//            $job->delete();
//            return;
//        }

        //文章入库，存入文章表中;
        foreach ($articles as $vo) {
            $item = [];
            $item['title'] = $vo['title'];
            $item['description'] = $vo['description'];
            $item['keywords'] = $vo['keywords'];
            $item['content']  = $vo['content'];
            $item['read_count'] = 0;
            $item['user_id'] = $uid;
            $item['status'] = ArticleModel::STATUS_DRAFT;
            $item['category_ids'] = [$categoryId];

            $article = new ArticleModel();
            $article->add($item);
        }

        Log::info('文章成功入库，数量：' . count($articles));


        //再次发送消息，进行文章内容采集；
        $jobHandlerClass  = 'app\admin\job\Crawler@crawlArticles';
        $jobData = ['id' => $id, 'uid' => $uid, 'create_time' => date_time()];
        $jobQueue = config('queue.default');
        $isPushed = Queue::push($jobHandlerClass, $jobData, $jobQueue);
    }

    //定时采集
    public function timingCrawl(Job $job, $data)
    {

    }

    //*****************静态业务逻辑，供Job及command调用**********************
    //抓取文章网址，返回列表数组
    public static function crawlUrls($url, $articleUrl, $isPaging, $startPage, $endPage, $pagingUrl)
    {
        $urlArr = explode('/', $url);
        $protocol = str_replace(':', '', $urlArr[0]);
        $baseUrl = $protocol . ':' . '//' . $urlArr[2];

        $urls = [];
        if ($isPaging) {
            for ($i = $startPage; $i <= $endPage; $i++) {
                $urls[] = str_replace('{page}', $i, $pagingUrl);
            }
        } else {
            $urls[] = $url;
        }
        //dump($urls);

        $articleUrls = [];
        //采集urls中的文章网址
        $ql = QueryList::getInstance();
        foreach ($urls as $v) {
            Log::info("采集 $v");
            //采集规则, '规则名1' => ['选择器1','元素属性'],
            $rules = [
                'url' => explode(',', $articleUrl)
            ];
            //dump($rules);
            $result = $ql->get($v)->rules($rules)->queryData();
            //dump($result);
            //dump($ql->getHtml());
            Log::info($result);
            if (empty($result)) {
                Log::info("采集 $v , 未找到数据");
            }

            foreach ($result as $vo) {
                $val = $vo['url'];
                if (strpos($val, '//') === 0) {
                    $val = $protocol . ':' . $val;
                } else if (strpos($val, '/') === 0) {
                    $val = $baseUrl . $val;
                }
                $articleUrls[] = $val;
            }
        }

        return $articleUrls;
    }

    //抓取文章内容，返回数组
    public static function crawlArticle($url, $encoding, $articleTitle, $articleDescription, $articleKeywords, $articleContent, $articleAuthor, $articleImage='')
    {
        //采集urls中的文章网址
        $ql = QueryList::getInstance();

        Log::info("采集 $url");
        //采集规则, '规则名1' => ['选择器1','元素属性'],
        $rules = [
            'title' => explode(',', $articleTitle),
            'description' => explode(',', $articleDescription),
            'keywords' => explode(',', $articleKeywords),
            'content' => explode(',', $articleContent),
        ];
        if (!empty($articleAuthor)) {
            $rules['author'] = explode(',', $articleAuthor);
        }

        Log::info($rules);
        //Log::info($ql->get($url)->getHtml());
        if (empty($encoding) || $encoding == 'auto') {
            //配置不进行ssl验证
            $streamOpts = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ]
            ];
            $html = file_get_contents($url, false, stream_context_create($streamOpts));
            //dump($html);
            $encoding = Crawler::getEncoding($html);
            //dump($encoding);
            $result = $ql->html($html)->encoding($encoding)->rules($rules)->queryData();
        } else {
            $result = $ql->get($url)->encoding($encoding)->rules($rules)->queryData();
        }
        //Log::info($result);
        if (empty($result)) {
            Log::info("采集 $url , 未找到数据");
            return false;
        }

        $article = $result[0];
        $article['title'] = isset($article['title']) ? mb_convert_encoding($article['title'], 'utf-8', $encoding) : '';
        $article['description'] = isset($article['description']) ? mb_convert_encoding($article['description'], 'utf-8', $encoding) : '';
        $article['content'] = isset($article['content']) ? mb_convert_encoding($article['content'], 'utf-8', $encoding) : '';
        $article['keywords'] = isset($article['keywords']) ? mb_convert_encoding($article['keywords'], 'utf-8', $encoding) : '';
        $article['author'] = isset($article['author']) ? mb_convert_encoding($article['author'], 'utf-8', $encoding) : '';

        //清除xss元素及内容
        $article['content'] = htmlspecialchars_decode(remove_xss($article['content']));

        return $article;
    }

    public static function getEncoding($html)
    {
        $text = $html;
        if (preg_match('/^[a-zA-Z]+:\/\/(\w+(-\w+)*)(\.(\w+(-\w+)*))*(\?\s*)?$/', $html)) {
            $text = file_get_contents($html);
        }
        $encoding = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $text,$matches) ? strtolower($matches[1]) : "";
        return $encoding;
    }

}