<?php
namespace app\admin\controller;

use think\facade\Env;
use think\facade\Log;

use app\frontend\controller\Base;
use app\common\model\cms\ArticleModel;
use app\common\model\cms\CategoryModel;
use app\common\model\ConfigModel;

use app\common\library\LibSitemap;

/**
 * 站长工具
 * Webmaster class
 */
class Webmaster extends Base
{
    private $config = [
        "domain" => '',
        "xml_file" => "_sitemap", //不带后缀
    ];

    public function index()
    {
        return $this->fetch('webmaster/index');
    }

    //站长设置
    public function baidu()
    {
        $tab = input('param.tab', 'setting');
        if ($tab == 'setting') {
            $zhanzhang_site = input("post.zhanzhang_site", '');
            $zhanzhang_token = input("post.zhanzhang_token", '');
    
            $ConfigModel = new ConfigModel();
            $ConfigModel->where('key', 'zhanzhang_site')->setField('value', $zhanzhang_site);
            $ConfigModel->where('key', 'zhanzhang_token')->setField('value', $zhanzhang_token);
    
            cache('config', null);

            $this->success('操作成功');
        } else if ($tab == 'push-urls') {
            $zhanzhang_site = get_config('zhanzhang_site', '');
            $zhanzhang_token = get_config('zhanzhang_token', '');
            if (empty($zhanzhang_site) || empty($zhanzhang_token)) {
                $this->error("zhanzhang_site 或 zhanzhang_token  未配置！！！");
            }

            $urls = input("post.urls", '');
            if (empty($urls)) {
                $this->error("urls地址不能为空!");
            }

            $api = "http://data.zz.baidu.com/urls?site=$zhanzhang_site&token=$zhanzhang_token";
            $output = http_post($api, $urls);
            //dump($output);
            Log::info($output);

            $res = json_decode($output, true);
            Log::info($res);
            if (isset($res['error'])) {
               $this->error($res['message']);
            }
            if (isset($res['success'])) {
                $this->success("操作成功, 成功 {$res['success']} 个");
            }
            
        }        

    }

    //sitemap xml生成工具
    public function sitemap($pageSize, $maxPage)
    {
        $xmlFileName = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $this->config['xml_file'];
        //清除旧的文件
        if (file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT)) {
            foreach(glob($xmlFileName . "*") as $file) {
                unlink($file);
            }
        }

        // 计算生成时间
        $costTimeStart = millisecond();

        $sitemap = new LibSitemap($this->config['domain'] ? $this->config['domain'] : config('url_domain_root'));
        $sitemap->setXmlFile($xmlFileName);	 // 设置xml文件（可选）
        $sitemap->setDomain($this->config['domain'] ? $this->config['domain'] : config('url_domain_root')); // 设置自定义的根域名（可选）
        $sitemap->setIsSchemeMore(true);	// 设置是否写入额外的Schema头信息（可选）


        //生成index 首页
        $sitemap->addItem(url('frontend/Index/index', null, false, get_config('domain_name')), 1, "hourly", date_time());
        $sitemap->addItem(url('frontend/Index/about', null, false, get_config('domain_name')), 1, "monthly", date_time());
        $sitemap->addItem(url('frontend/Index/contact', null, false, get_config('domain_name')), 1, "monthly", date_time());
        $sitemap->addItem(url('frontend/Index/about', null, false, get_config('domain_name')), 1, "monthly", date_time());

        //生成栏目item
        $CategoryModel = new CategoryModel();
        $resultSet = $CategoryModel->where(['status' => CategoryModel::STATUS_ONLINE])->order('sort asc')->select();
        foreach ($resultSet as $category) {
            $priority = LibSitemap::$PRIORITY[1];
            $loc = url('cms/Article/articleList', ['cid' => $category->id], false, get_config('domain_name'));
            $sitemap->addItem($loc, $priority, "daily", date_time());

            $loc = url('cms/Article/articleList', ['cname' => $category->name], false, get_config('domain_name'));
            $sitemap->addItem($loc, $priority, "daily", date_time());
        }

        //生成文章item
        $ArticleModel = new ArticleModel();
        $where = [
            'status' => ArticleModel::STATUS_PUBLISHED
        ];
        $resultSet = $ArticleModel->where($where)->order('sort desc, id desc')->select();
        foreach ($resultSet as $article) {
            $priority = LibSitemap::$PRIORITY[2];
            $loc = url('cms/Article/viewArticle', ['aid' => $article->id], false, get_config('domain_name'));
            $sitemap->addItem($loc, $priority, "weekly", $article->update_time);
        }

        //sitemap_xml_hook 函数来实现hook sitemap，提供外部的url项目写入
        //外部建议，把sitemap_xml_hook函数定义在common_business.php中
        if (function_exists('sitemap_xml_hook')) {
            sitemap_xml_hook($sitemap, $pageSize, $maxPage);
        }

        $sitemap->endSitemap();

        //生成sitemap index;
        $sitemapLoc = url('cms/Sitemap/xml', null, false, get_config('domain_name'));
        $sitemapLoc = substr($sitemapLoc, 0, strlen($sitemapLoc) - 4);
        $sitemap->createSitemapIndex($sitemapLoc);

        // 计算生成的时间
        $costTime = millisecond() - $costTimeStart;
        $costTime= sprintf('%01.2f', $costTime);

        $this->success("生成sitemap成功，用时 : $costTime (ms)");
    }
}