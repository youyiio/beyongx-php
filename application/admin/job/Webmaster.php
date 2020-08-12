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

use QL\QueryList;

class Webmaster
{

    private $error;

    /**
     * 检测文章索引情况，执行job
     * @param Job $job
     * @param $data
     * @throws \Exception
     */
    public function checkIndex(Job $job, $data)
    {
        Log::info("job[{$data['create_time']}] 检测文章索引,Queue Job开始...");

        $aid = $data['id'];
        if (empty($aid)) {
            $job->delete();
            return;
        }

        $article = ArticleModel::find($aid);
        if (!$article) {
            Log::info("文章: $aid 未找到");
            $job->delete();
            return;
        }

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章: $aid 状态未发布");
            $job->delete();
            return;
        }

        //$url = url('cms/Article/viewArticle', ['aid' => $aid], true, get_config('domain_name')); //job中使用url，获取异常
        //$url = get_config('domain_name') . url('cms/Article/viewArticle', ['aid' => $id], true, false); //hack
        $url = $data['url'];
        $indexed = self::baiduCheckIndex($url);
        if ($indexed) {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 1);
        } else {
            $article->meta(ArticleMetaModel::KEY_BAIDU_INDEX, 0);
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
        Log::info("job[{$data['create_time']}] 提交链接,Queue Job开始...");

        $urls = [];
        if (isset($data['urls'])) {
            $urls = $data['urls'];
        } else if (isset($data['url'])) {
            $urls[] = $data['url'];
        }

        self::baiduPushLinks($urls);

        $job->delete();
        Log::info('提交链接已完成');
    }

    //*****************静态业务逻辑，供Job及command调用**********************
    //检测url是否收录收录验证
    public static function baiduCheckIndex($url = '', $fine=true)
    {
        $count = 0;
        $spUrl = "https://www.baidu.com/s?wd=" . urlencode($url);

        //精确
        if ($fine) {
            $domain = url_get_domain($url);
            $sorts = self::getTargetUrl($domain, $spUrl, 'bd');
            $sorts && $count = count($sorts);
        } else {
            $output = self::httpGet($spUrl);
            $preg = '/百度为您找到相关结果约(\d+)个/';
            $check = preg_match($preg, $output, $arr);
            $check && $count = $arr[1];
        }

        return $count; //返回收录数量
    }

    //链接推送，实时收录, links = [$url1, $url2...]
    public static function baiduPushLinks($links = [])
    {
        $site = get_config('zhanzhang_site', '');
        $token = get_config('zhanzhang_token', '');
        if (empty($site) || empty($token)) {
            Log::info("zhanzhang_site 或 zhanzhang_token  未配置！！！");
            return false;
        }

        $api = "http://data.zz.baidu.com/urls?site=$site&token=$token";

        $output = self::httpPost($api, implode("\n", $links));
        Log::debug($api);
        Log::debug($links);
        Log::debug($output);

        return $output;
    }

    //site指令：域名收录情况
    public static function siteCmd($domain, $sp, $source='pc')
    {
        if ($sp == 'bd') {
            return self::baiduSiteCmd($domain);
        } elseif ($sp == 'so') {
            return self::soSiteCmd($domain);
        } else if ($sp == 'sg') {
            return self::sogouSiteCmd($domain);
        } else {
            //$this->error('未实现');
            return -1;
        }
    }

    //百度site指令, 返回收录数量
    public static function baiduSiteCmd($domain = '')
    {
        $url = "https://www.baidu.com/s?wd=site:$domain";
        $output = self::httpGet($url);
        //echo $output;
        $preg = '/找到相关结果数约(\d+)个/';
        $check = preg_match($preg, $output, $arr);
        if (!$check) {
            //$preg = '/该网站共有(\w+)个网页被百度收录/';
            $preg = '/<b style=\"color:#333\">([0-9,\x{4e00}-\x{9fa5}]{1,})<\/b>/u'; //中文识别：\x{4e00}-\x{9fa5} ，需要启动/u, 进行utf-8识别
            $check = preg_match($preg, $output, $arr);
        }
        if (!$check) {
            //$this->error = '网站未被收录';
            return -1;
        }
        $sites = $arr[1];
        $sites = str_replace(',', '', $sites);

        return $sites;
    }

    public static function getTargetUrl($domain, $spUrl, $sp)
    {
        $output = self::httpGet($spUrl);
        if ($output == false) {
            //页面获取失败
            return false;
        }
        switch ($sp) {
            case 'bd':
                $rules = [
                    'target' => ['.f13 a:eq(0)','text'],
                ];
                $range = '.result';
                break;
            case 'mb':
                $rules = [
                    'title' => array('.c-title.c-gap-top-small','text'),
                    'target' => ['span.c-showurl','text'],
                ];
                $range = '.result';
                break;
            case 'so':
                $rules = [
                    'target' => ['.res-linkinfo cite','text'],
                ];
                $range = 'ul.result>li.res-list';
                break;
            case 'sg':
                $rules = [
                    'target' => ['cite','text'],
                ];
                $range = '.results .fb';
                break;
            default:
                //未知任务类型
                return false;
                break;
        }

        //防止出现警告：DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';'
        libxml_use_internal_errors(true);
        $ql = QueryList::html($output)->rules($rules)->range($range)->query();
        $pageList = $ql->getData();

        $res = self::getSort($domain, $pageList);
        if (empty($res)) {
            //未找到结果页地址
            return false;
        }

        return $res;
    }

    /**
     * 筛选属于用户的条目及其排名
     * @param  $domainOrName $domainOrName   用户网站域名|标识
     * @param  array $pageList 搜索结果条目数组
     * @return array           筛选出来对应的结果
     */
    protected static function getSort($domainOrName, $pageList)
    {
        $res = [];
        if ($domainOrName) {
            // 筛选属于用户的条目及其排名
            foreach ($pageList as $k => $v) {
                if (preg_match("/$domainOrName/", $v['target'])) {
                    $res[] = [
                        'sort' => $k + 1,
                        'target' => preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)$/','',$v['target']), //去除尾巴空格
                    ];
                }
            }
        } else {
            foreach ($pageList as $k => $v) {
                $res[] = [
                    'sort' => $k + 1,
                    'target' => preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)$/','',$v['target']), //去除尾巴空格
                ];
            }
        }

        return $res;
    }

    //get访问
    private static function httpGet($url)
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
    private static function httpPost($url, $requestData=array())
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