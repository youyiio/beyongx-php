<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-06-03
 * Time: 15:12
 */

namespace app\admin\job;

use think\facade\Log;
use think\queue\Job;

use app\common\model\ArticleModel;
use app\common\model\ArticleMetaModel;

class Webmaster
{

    /**
     * 检测文章索引情况，执行job
     * @param Job $job
     * @param $data
     * @throws \Exception
     */
    public function checkIndex(Job $job, $data)
    {
        Log::info('检测文章索引,Queue Job开始...');

        $articleId = $data['id'];
        if (empty($articleId)) {
            $job->delete();
            return;
        }

        $article = ArticleModel::find($articleId);
        if (!$article) {
            Log::info("文章: $articleId 未找到");
            $job->delete();
            return;
        }

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章: $articleId 状态未发布");
            $job->delete();
            return;
        }

        //$url = url('cms/Article/viewArticle', ['aid' => $articleId], true, get_config('domain_name')); //job中使用url，获取异常
        $url = $data['url'];
        $indexed = $this->baiduCheckIndex($url);
        if ($indexed) {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, $indexed);
        }

        $job->delete();
        Log::info('文章索引检测已完成');
    }

    /**
     * 实时推送提交链接，执行job
     * @param Job $job
     * @param $data
     * @throws \Exception
     */
    public function pushLinks(Job $job, $data)
    {
        Log::info('提交链接,Queue Job开始...');

        $urls = [];
        if (isset($data['urls'])) {
            $urls = $data['urls'];
        } else if (isset($data['url'])) {
            $urls[] = $data['url'];
        }

        $this->baiduPushLinks($urls);

        $job->delete();
        Log::info('提交链接已完成');
    }

    //链接推送，实时收录, links = [$url1, $url2...]
    public function baiduPushLinks($links = [])
    {
        $site = get_config('zhanzhang_site', '');
        $token = get_config('zhanzhang_token', '');
        if (empty($site) || empty($token)) {
            Log:: info("zhanzhang_site 或 zhanzhang_token  未配置！！！");
            return false;
        }

        $api = "http://data.zz.baidu.com/urls?site=$site&token=$token";

        $output = $this->httpPost($api, implode("\n", $links));
        Log::debug($api);
        Log::debug($output);

        return $output;
    }

    //检测url是否收录收录验证
    public function baiduCheckIndex($url = '')
    {
        $count = 0;

        $url = "https://www.baidu.com/s?wd=" . urlencode($url);
        $output = $this->httpGet($url);
        $preg = '/百度为您找到相关结果约(\d+)个/';
        $check = preg_match($preg, $output, $arr);
        if ($check) {
            $count = $arr[1];
        }

        return $count; //返回收录数量
    }

    //域名收录情况
    public function domainSite($domain, $sp, $source='pc')
    {
        if ($sp == 'bd') {
            return $this->baiduDomainSite($domain);
        } elseif ($sp == 'so') {
            return $this->soDomainSite($domain);
        } else if ($sp == 'sg') {
            return $this->sogouDomainSite($domain);
        } else {
            $this->error('未实现');
        }
    }

    //百度域名收录情况
    public function baiduDomainSite($domain = '')
    {
        $url = "https://www.baidu.com/s?wd=site:$domain";
        $output = $this->httpGet($url);
        //echo $output;
        $preg = '/找到相关结果数约(\d+)个/';
        $check = preg_match($preg, $output, $arr);
        if (!$check) {
            //$preg = '/该网站共有(\w+)个网页被百度收录/';
            $preg = '/<b style=\"color:#333\">([0-9,\x{4e00}-\x{9fa5}]{1,})<\/b>/u'; //中文识别：\x{4e00}-\x{9fa5} ，需要启动/u, 进行utf-8识别
            $check = preg_match($preg, $output, $arr);
        }
        if (!$check) {
            return $this->error('网站未被收录,请先添加PC流量或移动流量任务');
        }
        $sites = $arr[1];
        $sites = str_replace(',', '', $sites);
        return $this->success($sites); //返回收录数量
    }

    //百度PC权重
    public function baiduPcRank($domain = '')
    {
        //$domain = input('domain/s');
        if (empty($domain) || !preg_match($this->domainPreg,$domain)) {
            return $this->error('域名格式错误');
        }
        $cacheMark = 'bdRank_'.md5($domain);
        if (cache("?$cacheMark") && !config('app_debug')) {
            return $this->success(cache($cacheMark));
        }
        $url = 'http://rank.chinaz.com/';
        $url = $url . $domain;
        $output = $this->httpGet($url);
        if ($output == false) {
            return $this->error('数据抓取失败');
        }
        // dump($output);die;
        // $ql = QueryList::run('Request',[
        //     'http' => [
        //         'target' => $url,
        //         'referrer'=>'http://tool.chinaz.com/',
        //         'method' => 'GET',
        //         'timeout' => '30',
        //         'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'
        //     ],
        // ]);
        $range = '.ResultListWrap .ReLists';
        $rules = [
            'seUrl' => ['div:eq(0) a:eq(0)','href'],
            'keyword' => ['div:eq(0) a:eq(0)','text'],
            'index' => ['div.w8-0:eq(1)','text'],
            'sort' => ['div.w8-0:eq(3)','text','','num_only'],
        ];

        $ql = QueryList::html($output)->rules($rules)->range($range)->query();
        $data = $ql->data;
        if (empty($data)) {
            return $this->error('未找到相关排名');
        }
        cache($cacheMark, $data,86400);
        return $this->success($data);
    }

    //百度移动权重
    public function baiduMobileRank($domain = 'www.rbhj.com')
    {
        //$domain = input('domain/s');
        if (empty($domain) || !preg_match($this->domainPreg,$domain)) {
            return $this->error('域名格式错误');
        }
        $cacheMark = 'mb_rank_'.md5($domain);
        if (cache("?$cacheMark") && !config('app_debug')) {
            return $this->success(cache($cacheMark));
        }
        $url = 'http://rank.chinaz.com/baidumobile/';
        $url = $url . $domain;
        $output = $this->httpGet($url);
        if ($output == false) {
            return $this->error('数据抓取失败');
        }
        /*$ql = QueryList::run('Request',[
            'http' => [
                'target' => $url,
                'referrer'=>'http://tool.chinaz.com/',
                'method' => 'GET',
                'timeout' => '30',
            ],
        ]);*/
        $range = '.ResultListWrap .ReLists';
        $rules = [
            'seUrl' => ['div.wTipWrap a','href'],
            'keyword' => ['div.wTipWrap a','text'],
            'index' => ['div.w14-0:eq(0)','text'],
            'sort' => ['div.w13-0:eq(0) a','text','','num_only'],
        ];
        $ql = QueryList::Query($output,$rules,$range);
        $data = $ql->data;
        if (empty($data)) {
            return $this->error('未找到相关排名');
        }
        cache($cacheMark, $data, 86400);
        return $this->success($data);
    }

    //360域名收录情况
    public function sogouDomainSite($domain = '')
    {
        $url = "https://www.sogou.com/web?query=site:$domain";
        $output = $this->httpGet($url);
        // echo $output;
        $preg = '/找到约(\d+)条结果/';
        $check = preg_match($preg, $output, $arr);
        if (!$check) {
            return $this->error('网站未被收录,请先添加PC流量或移动流量任务');
        }
        return $this->success($arr[1]); //返回收录数量
    }

    //360域名收录情况
    public function soDomainSite($domain = '')
    {
        $url = "https://www.so.com/s?q=site:$domain";
        $output = $this->httpGet($url);
        // echo $output;
        $preg = '/该网站约(\d+)个网页被360搜索收录/';
        $check = preg_match($preg, $output, $arr);
        if (!$check) {
            return $this->error('网站未被收录,请先添加PC流量或移动流量任务');
        }
        return $this->success($arr[1]); //返回收录数量
    }

    //360PC权重
    public function soPcRank($domain = 'www.rbhj.com')
    {
        //$domain = input('domain/s');
        if (empty($domain) || !preg_match($this->domainPreg,$domain)) {
            return $this->error('域名格式错误');
        }
        $cacheMark = 'soPcRank_'.md5($domain);
        if (cache("?$cacheMark") && !config('app_debug')) {
            return $this->success(cache($cacheMark));
        }
        $url = 'http://rank.chinaz.com/sorank/';
        $url = $url . $domain;
        $output = $this->httpGet($url);
        if ($output == false) {
            return $this->error('数据抓取失败');
        }
        /*$ql = QueryList::run('Request',[
            'http' => [
                'target' => $url,
                'referrer'=>'http://tool.chinaz.com/',
                'method' => 'GET',
                'timeout' => '30',
            ],
        ]);*/
        $range = '.ResultListWrap .ReLists';
        $rules = [
            'seUrl' => ['div:eq(0) a','href'],
            'keyword' => ['div:eq(0) a','text'],
            'index' => ['div:eq(1)','text'],
            'sort' => ['div:eq(2) a','text','','num_only'],
        ];
        $ql = QueryList::Query($output,$rules,$range);
        $data = $ql->data;
        if (empty($data)) {
            return $this->error('未找到相关排名');
        }
        cache($cacheMark,$data,86400);
        return $this->success($data);
    }

    //360移动权重
    public function soMobileRank($domain = 'www.rbhj.com')
    {
        //$domain = input('domain/s');
        if (empty($domain) || !preg_match($this->domainPreg,$domain)) {
            return $this->error('域名格式错误');
        }
        $cacheMark = 'smRank_'.md5($domain);
        if (cache("?$cacheMark") && !config('app_debug')) {
            return $this->success(cache($cacheMark));
        }
        $url = 'http://rank.chinaz.com/rank360/';
        $url = $url . $domain;
        $output = $this->httpGet($url);
        if ($output == false) {
            return $this->error('数据抓取失败');
        }
        /*$ql = QueryList::run('Request',[
            'http' => [
                'target' => $url,
                'referrer'=>'http://tool.chinaz.com/',
                'method' => 'GET',
                'timeout' => '30',
            ],
        ]);*/
        $range = '.ResultListWrap .ReLists';
        $rules = [
            'seUrl' => ['div:eq(0) a','href'],
            'keyword' => ['div:eq(0) a','text'],
            'index' => ['div:eq(1)','text'],
            'sort' => ['div:eq(2) a','text','','num_only'],
        ];
        $ql = QueryList::Query($output,$rules,$range);
        $data = $ql->data;
        if (empty($data)) {
            return $this->error('未找到相关排名');
        }
        cache($cacheMark,$data,86400);
        return $this->success($data);
    }


    //get访问
    private function httpGet($url)
    {
        $header = [
            'User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 执行
        $content = curl_exec($ch);
        if ($content == false) {
            Log::error(curl_error($ch));
            return false;
        }
        // 关闭
        curl_close($ch);

        //输出结果
        return $content;
    }

    //get访问
    private function httpPost($url, $requestData=array())
    {
        $header = [
            'User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (is_array($requestData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        }

        // 执行
        $content = curl_exec($ch);
        if ($content == false) {
            Log::error(curl_error($ch));
            return false;
        }
        // 关闭
        curl_close($ch);

        //输出结果
        return $content;
    }
}