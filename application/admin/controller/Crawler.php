<?php
namespace app\admin\controller;

use app\common\model\CrawlerModel;
use app\common\model\CategoryModel;
use think\Queue;

/**
 采集控制器
 */
class Crawler extends Base
{

    public function index()
    {
        $crawlerModel = new CrawlerModel();
        $where = [
            ['status', '>', CrawlerModel::STATUS_DELETED],
        ];
        $list = $crawlerModel->where($where)->order('id desc')->paginate(10);

        $this->assign('list', $list);
        $this->assign('page', $list->render());

        return $this->fetch('index');
    }

    //添加规则
    public function create()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $data['is_timing'] = isset($data['is_timing']) && $data['is_timing'] == 'on' ? true : false;
            $data['is_paging'] = isset($data['is_paging']) && $data['is_paging'] == 'on' ? true : false;

            $check = validate('Crawler')->scene('add')->check($data);
            if ($check !== true) {
                $this->error(validate('Crawler')->getError());
            }

            $CrawlerModel = new CrawlerModel();
            $res = $CrawlerModel->save($data);
            if ($res === true) {
                $this->success('成功添加新规则', url('crawler/index'));
            } else {
                $this->error($CrawlerModel->getError());
            }
        }

        //分类列表
        $CategoryModel = new CategoryModel();
        $cateList = $CategoryModel->getTreeData('tree', 'sort,id', 'title_cn');

        $this->assign('categoryList', $cateList);

        return $this->fetch('create');
    }

    //编辑
    public function edit()
    {
        $id = input('param.id', 0);
        if (empty($id)) {
            $this->error('参数错误');
        }
        $crawler = CrawlerModel::get($id);
        if (!$crawler) {
            $this->error('采集规则不存在！');
        }

        $CrawlerModel = new CrawlerModel();

        if (request()->isAjax()) {
            $data = input('post.');
            $data['is_timing'] = isset($data['is_timing']) && $data['is_timing'] == 'on' ? true : false;
            $data['is_paging'] = isset($data['is_paging']) && $data['is_paging'] == 'on' ? true : false;

            $check = validate('Crawler')->scene('edit')->check($data);
            if ($check !== true) {
                $this->error(validate('Crawler')->getError());
            }

            $res = $CrawlerModel->allowField(true)->isUpdate(true)->save($data);
            if ($res === true) {
                $this->success('规则修改成功！', url('Crawler/index'));
            } else {
                $this->error('修改失败！');
            }
        }

        $crawler = CrawlerModel::get($id);
        $this->assign('crawler', $crawler);

        $CategoryModel = new CategoryModel();
        $cateList = $CategoryModel->getTreeData('tree', 'sort,id', 'title_cn');
        $this->assign('categoryList', $cateList);

        return $this->fetch('create');
    }

    //采集规则测试
    public function crawlTest()
    {
        $categoryId = input('get.category_id/d');

        $data = input('get.');
        $url = $data['url'];
        $encoding = $data['encoding'];
        $isTiming = isset($data['is_timing']) && $data['is_timing'] == 'on' ? true : false;
        $isPaging = isset($data['is_paging']) && $data['is_paging'] == 'on' ? true : false;

        $startPage = input('get.start_page/d');
        $endPage = input('get.end_page/d');
        $pagingUrl = input('get.paging_url/s');

        $articleUrl = input('get.article_url/s');
        $articleTitle = input('get.article_title/s');
        $articleDescription = input('get.article_description/s');
        $articleKeywords = input('get.article_keywords/s');
        $articleContent = input('get.article_content/s');
        $articleAuthor = input('get.article_author/s');
        $articleImage = input('get.article_image/s');

        $data = input('get.');
        $data['is_timing'] = isset($data['is_timing']) && $data['is_timing'] == 'on' ? true : false;
        $data['is_paging'] = isset($data['is_paging']) && $data['is_paging'] == 'on' ? true : false;

        $check = validate('Crawler')->scene('test')->check($data);
        if ($check !== true) {
            $this->error(validate('Crawler')->getError(), 'javascript:void(0)'); //不做跳转
        }

        try {
            $endPage = $isPaging ? $startPage : $endPage; //测试抓取时，分页只抓取一页的urls
            $urls = \app\admin\job\Crawler::crawlUrls($url, $articleUrl, $isPaging, $startPage, $endPage, $pagingUrl);
            //dump($urls);
            if (empty($urls)) {
                $this->error('未采集到文章网址', 'javascript:void(0)');
            }

            $contentUrl = $urls[0];
            $result = \app\admin\job\Crawler::crawlArticle($contentUrl, $encoding, $articleTitle, $articleDescription, $articleKeywords, $articleContent, $articleAuthor, $articleImage);
            //dump($result);

            $this->assign('article', $result);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->error($error, 'javascript:void(0)');
        }

        return $this->fetch('crawler/crawlTest');
    }

    //开始采集
    public function startCrawl()
    {
        $id = input('id/d', 0);
        $crawler = CrawlerModel::get($id);
        if (!$crawler) {
            $this->error('采集规则不存在');
        }

        //更新采集状态
        $crawler->status = CrawlerModel::STATUS_CRAWLING;
        $crawler->save();

        //指定任务的处理类，若指定至方法时，@methodName
        $jobHandlerClass  = 'app\admin\job\Crawler@startCrawl';
        //任务的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串; jobData 为对象时，存储其public属性的键值对
        $jobData = ['id' => $id, 'uid' => $this->uid, 'create_time' => date_time()];
        //任务归属的队列名称，如果为新队列，会自动创建
        $jobQueue = config('queue.default');

        $isPushed = Queue::push($jobHandlerClass, $jobData, $jobQueue);
        // database 驱动时，返回值为 1|false; redis 驱动时，返回值为 随机字符串|false
        if ($isPushed !== false) {
            $this->success('采集任务已经启动...');
        } else {
            $this->error('采集失败！');
        }
    }

    //删除采集规则
    public function deleteCrawler()
    {
        $cid = input('id/d',0);
        if ($cid <= 0) {
            $this->error('参数错误');
        }

        $res = CrawlerModel::where('id', $cid)->setField('status', CrawlerModel::STATUS_DELETED);
        if ($res) {
            $this->success('成功删除规则');
        } else {
            $this->error('删除失败');
        }
    }

    //克隆采集规则
    public function cloneCrawler()
    {
        $cid = input('id/d',0);
        if ($cid <= 0) {
            $this->error('参数错误');
        }

        $crawler = CrawlerModel::get($cid);
        if (empty($crawler)) {
            $this->error('采集规则不存在!');
        }

        $data = $crawler->toArray();
        unset($data['id']);
        $data['title'] = $data['title'] . ' 副本';
        $data['update_time'] = date_time();
        $data['create_time'] = date_time();

        $CrawlerModel = new CrawlerModel();
        $res = $CrawlerModel->save($data);
        if ($res) {
            $this->success('克隆规则成功!');
        } else {
            $this->error('克隆规则失败');
        }
    }
}

