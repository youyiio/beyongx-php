<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-12-03
 * Time: 16:15
 */
namespace app\cms\controller;

use think\facade\Env;
use think\facade\Log;

use app\frontend\controller\Base;
use app\common\model\cms\ArticleModel;
use app\common\model\cms\CategoryModel;

use app\common\library\LibSitemap;

class Sitemap extends Base
{

    private $config = [
        "domain" => '',
        "xml_file" => "_sitemap", //不带后缀
    ];

    //网站地图，生成sitemap.xml, 500url分一个文件;避免sitemap.xml过大
    public function xml($id='')
    {
        header("Content-type:text/xml;charset=utf-8");

        $xmlFileName = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $this->config['xml_file'];
        if (file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT)) {
            if (!empty($id) && file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . $id . LibSitemap::SITEMAP_EXT)) {
                echo file_get_contents($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . $id . LibSitemap::SITEMAP_EXT);
                exit;
            } else if (file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT)) {
                echo file_get_contents($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT);
                exit;
            }
        }

        // 计算生成时间
        $costTimeStart = $this->getMillisecond();

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
            sitemap_xml_hook($sitemap);
        }

        $sitemap->endSitemap();

        //生成sitemap index;
        $sitemapLoc = url('cms/Sitemap/xml', null, false, get_config('domain_name'));
        $sitemapLoc = substr($sitemapLoc, 0, strlen($sitemapLoc) - 4);
        $sitemap->createSitemapIndex($sitemapLoc);

        // 计算生成的时间
        $costTime = $this->getMillisecond() - $costTimeStart;
        $costTime= sprintf('%01.1f', $costTime);
        Log::info("生成sitemap.xml 用时 : $costTime (ms)");

        if (file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT)) {
            if (!empty($id) && file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . $id . LibSitemap::SITEMAP_EXT)) {
                echo file_get_contents($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . $id . LibSitemap::SITEMAP_EXT);
            } else if (file_exists($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT)) {
                echo file_get_contents($xmlFileName . LibSitemap::SITEMAP_SEPERATOR . LibSitemap::INDEX_SUFFIX . LibSitemap::SITEMAP_EXT);
            } else {
                echo 'xml file is not exist!';
            }
            exit;
        }
        exit;
    }

    //网站地图，html页面
    public function html()
    {
        $templateFile = Env::get('app_path') . 'cms' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'sitemap' . DIRECTORY_SEPARATOR . 'sitemap.html';
        $content = file_get_contents($templateFile);
        return $this->display($content);
    }

    //  获取毫秒的时间戳
    private function getMillisecond()
    {
        $time = explode(" ", microtime());
        return $time[1] + $time[0];
    }


}
