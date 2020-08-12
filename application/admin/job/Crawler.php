<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-03-17
 * Time: 17:33
 */

namespace app\admin\job;

use app\common\model\CrawlerMetaModel;
use think\facade\Env;
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
        Log::info("job[{$data['create_time']}] 采集文章网址Job开始...");

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
            if ($temp) { //&& $temp['remark'] == CrawlerMetaModel::STATUS_COMPLETE
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
        Log::info("job[{$data['create_time']}] 采集文章Job开始...");

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

            $article = [];
            try {
                $article = Crawler::crawlArticle($url, $encoding, $articleTitle, $articleDescription, $articleKeywords, $articleContent, $articleAuthor, $articleImage);
            } catch (\Exception $e) {
                Log::info('文章内容抓取发生错误: ' . $e->getTraceAsString());
                Log::error($e->getTraceAsString());
            }

            if ($article) {
                $article['meta_id'] = $meta['id'];
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
            $item['content'] = $vo['content'];
            $item['author'] = $vo['author'];
            $item['read_count'] = 0;
            $item['uid'] = $uid;
            $item['status'] = ArticleModel::STATUS_CRAWLED;
            $item['category_ids'] = [$categoryId];

            $article = new ArticleModel();
            $article->add($item);

            //获取自增Id,存入crawler_meta表
            $articleId = $article->id;
            $CrawlerMeta = CrawlerMetaModel::get($vo['meta_id']);
            $CrawlerMeta->article_id = $articleId;
            $CrawlerMeta->save();
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
        Log::info("job[{$data['create_time']}] 定时抓取开始....");

        Log::info('定时抓取结束!');
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
            Log::info('crawlUrls content length: ' . strlen($ql->getHtml()));
            Log::info($result);
            if (empty($result)) {
                Log::info("采集 $v , 未找到文章网址数据");
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

        //抓取图片
        $doc = \phpQuery::newDocumentHTML($article['content']);
        $imgs = pq($doc)->find( 'img');
        if (count($imgs) > 0) {
            foreach ($imgs as $img) {
                $src = pq($img)->attr( 'src');
                $src = self::getFullUrl($url, $src);
                $saveResult = json_decode(self::saveRemoteImage($src), true);
                Log::info('保存远程图片:');
                Log::info($saveResult);
                if ($saveResult['state'] === 'SUCCESS') {
                    $localSrc = $saveResult['url'];
                    pq($img)->attr( 'src', $localSrc);
                }
            }
            $article['content'] = $doc->htmlOuter();
        }

        return $article;
    }

    /**
     * 获取网页编码
     * @param $html
     * @return string
     */
    public static function getEncoding($html)
    {
        $text = $html;
        if (preg_match('/^[a-zA-Z]+:\/\/(\w+(-\w+)*)(\.(\w+(-\w+)*))*(\?\s*)?$/', $html)) {
            $text = file_get_contents($html);
        }
        $encoding = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $text,$matches) ? strtolower($matches[1]) : "";
        return $encoding;
    }

    /**
     * 获取完整地址
     * @param $browserUrl
     * @param $innerUrl
     * @return string
     */
    public static function getFullUrl($browserUrl, $innerUrl)
    {
        if (strpos($innerUrl, 'http') === 0) {
            return $innerUrl;
        }

        $urlArr = explode('/', $browserUrl);
        $protocol = str_replace(':', '', $urlArr[0]);
        $baseUrl = $protocol . ':' . '//' . $urlArr[2];

        $val = '';
        if (strpos($innerUrl, '//') === 0) {
            $val = $protocol . ':' . $innerUrl;
        } else if (strpos($innerUrl, '/') === 0) {
            $val = $baseUrl . $innerUrl;
        }

        return $val;
    }

    /**
     * 拉取远程图片
     * @param $fieldName imageurl地址
     * @return mixed
     */
    public static function saveRemoteImage($imgUrl)
    {
        //使用ueditor.json作为配置信息
        $configJson = file_get_contents(Env::get('config_path') . "ueditor.json");
        $configJson = preg_replace("/\/\*[\s\S]+?\*\//", "", $configJson);
        $CONFIG = json_decode($configJson, true);
        // 保留需要的数据
        $config = array(
            "pathFormat" => $CONFIG['catcherPathFormat'],
            "maxSize" => $CONFIG['catcherMaxSize'],
            "allowFiles" => $CONFIG['catcherAllowFiles'],
            "oriName" => "remote.png"
        );

        $rootPath = Env::get('root_path') . 'public';
        $savePath = DIRECTORY_SEPARATOR . 'upload'. DIRECTORY_SEPARATOR;


        $imgUrl = htmlspecialchars($imgUrl);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $data = array(
                'state' => '链接不是http|https链接',
            );
            return json_encode($data);
        }

        //设置get_headers/readfile 远程通信的配置
        stream_context_set_default([
            'http' => [
                //'header' => "Referer:$httpReferer",  //突破防盗链,不可用
                'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36', //突破防盗链
                'follow_location' => false // don't follow redirects
            ],
            'ssl' => [
                'verify_host' => false,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        //获取请求头并检测死链
        $heads = get_headers($imgUrl, true);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $data = array(
                'state' => '链接不可用',
            );
            return json_encode($data);
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr(strrchr($imgUrl,'/'), '.'));
        //img链接后缀可能为空,Content-Type须为image
        if ((!empty($fileType) && !in_array($fileType, $config['allowFiles'])) || stristr($heads['Content-Type'], "image") === -1) {
            $data = array(
                'state'=>'链接contentType不正确',
            );
            return json_encode($data);
        }

        //解析出域名作为http_referer
        $urlArr = explode('/', $imgUrl);
        $protocol = str_replace(':', '', $urlArr[0]);
        $httpReferer = $protocol . ':' . '//' . $urlArr[2];

        //打开输出缓冲区并获取远程图片
        ob_start();
        $res = false;
        $message = '';
        try {
            $res = readfile($imgUrl, false);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $img = ob_get_contents();
        ob_end_clean();

        if ($res === false) {
            $data = array(
                'state' => $message,
            );
            return json_encode($data);
        }

        //$m为文件名
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $savePath = $savePath . date('Ymd') . DIRECTORY_SEPARATOR;
        $dirname = $rootPath . $savePath;
        $file['oriName'] = $m ? $m[1]:"";
        $file['filesize'] = strlen($img);
        $file['ext'] = strtolower(strrchr($config['oriName'], '.'));
        $file['name'] = uniqid() . $file['ext'];
        $file['fullName'] = $dirname . $file['name'];
        $fullName = $file['fullName'];

        //检查文件大小是否超出限制
        if ($file['filesize'] >= ($config["maxSize"])) {
            $data = array(
                'state' => '文件大小超出网站限制',
            );
            return json_encode($data);
        }

        //创建目录失败
        if (!file_exists($dirname) &&
            !(mkdir($dirname, 0777, true) && chown($dirname, Env::get('run.user'))) ) {
            $data = array(
                'state' => '目录创建失败',
            );
            return json_encode($data);
        } else if (!is_writeable($dirname)) {
            $data = array(
                'state' => '目录没有写权限',
            );
            return json_encode($data);
        }

        //移动文件
        if (!(file_put_contents($fullName, $img) && file_exists($fullName))) { //移动失败
            $data = array(
                'state' => '写入文件内容错误',
            );
            return json_encode($data);
        } else { //移动成功
            $data = array(
                'state' => 'SUCCESS',
                'url' => config('view_replace_str.__PUBLIC__') . str_replace(DIRECTORY_SEPARATOR, '/', $savePath.$file['name']),
                'title' => $file['name'],
                'original' => $file['oriName'],
                'type' => $file['ext'],
                'size' => $file['filesize'],
            );
        }
        return json_encode($data);
    }
}