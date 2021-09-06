<?php
namespace app\admin\controller;

use app\common\model\cms\ArticleMetaModel;
use app\common\model\cms\ArticleModel;
use app\common\model\cms\CrawlerMetaModel;
use app\common\model\cms\CrawlerModel;
use app\common\model\cms\CategoryModel;
use think\Queue;
use think\Db;

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

    // 数据预处理,清洗替换
    public function preprocess()
    {
        $crawlerModel = new CrawlerModel();
        $crawlerList = $crawlerModel->where('status', '>', CrawlerModel::STATUS_WAITING)->order('id', 'desc')->select();
        $this->assign('crawlerList', $crawlerList);

        $crawlerId = input('crawlerId/d', -1);
        $replaceField = input('replaceField/s', ''); //查找范围, 文本或正则表达式（默认/regex/[Uixs]）
        $searchText = input('searchText/s', '');  //需要替换的文本
        $replaceText = input('replaceText/s', '');

        if (request()->isAjax()) {
            if ($crawlerId <= 0) {
                $this->error('请选择采集规则!');
            }

            if (empty($searchText) || empty($replaceText)) {
                $this->error('请输入替换规则!');
            }

            //判断是否正则表达式替换；TODO代码性能优化
            if (!preg_match('/^\/.+\/[Uixs]*/', $searchText)) {

                $count = CrawlerMetaModel::where('target_id', '=', $crawlerId)->count('id');
                if ($count > 100) {
                    //数据比较大时，暂时只支持mysql, replace/regexp_replace mysql的函数
                    $ArticleModel = new ArticleModel();

                    $data = [];
                    if ($replaceField == 'all') {
                        $data['title'] = Db::raw("replace(title, '$searchText', '$replaceText')");
                        $data['keywords'] = Db::raw("replace(keywords, '$searchText', '$replaceText')");
                        $data['description'] = Db::raw("replace(description, '$searchText', '$replaceText')");
                        $data['content'] = Db::raw("replace(content, '$searchText', '$replaceText')");
                    } else {
                        $data[$replaceField] = Db::raw("replace($replaceField, '$searchText', '$replaceText')");
                    }

                    $ArticleModel->where('id', 'in', function($query) use ($crawlerId) {
                        $query->table('cms_crawler_meta')->where('target_id', '=', $crawlerId)->field('article_id')->select();
                    })->cache('article_preprocess_replace_' . $replaceField)->update($data);

                } else {
                    $articleIds = CrawlerMetaModel::where('target_id', '=', $crawlerId)->column('article_id');
                    $articles = ArticleModel::where('id', 'in', $articleIds)->select();

                    foreach ($articles as $key => $article) {
                        if ($replaceField == 'all') {
                            $article->title = str_replace($searchText, $replaceText, $article['title']);
                            $article->keywords = str_replace($searchText, $replaceText, $article['keywords']);
                            $article->description = str_replace($searchText, $replaceText, $article['description']);
                            $article->content = str_replace($searchText, $replaceText, $article['content']);
                            $article->save();
                        } else {
                            $article->$replaceField = str_replace($searchText, $replaceText, $article[$replaceField]);
                            $article->save();
                        }
                    }
                }

                $this->success('数据文本替换成功！');
            } else {
                $count = CrawlerMetaModel::where('target_id', '=', $crawlerId)->count('id');
                if ($count > 100) {
                    $ArticleModel = new ArticleModel();

                    $data = [];
                    if ($replaceField == 'all') {
                        $data['title'] = Db::raw("regexp_replace(title, '$searchText', '$replaceText')");
                        $data['keywords'] = Db::raw("regexp_replace(keywords, '$searchText', '$replaceText')");
                        $data['description'] = Db::raw("regexp_replace(description, '$searchText', '$replaceText')");
                        $data['content'] = Db::raw("regexp_replace(content, '$searchText', '$replaceText')");
                    } else {
                        $data[$replaceField] = Db::raw("regexp_replace($replaceField, '$searchText', '$replaceText')");
                    }

                    $ArticleModel->where('id', 'in', function ($query) use ($crawlerId) {
                        $query->table('cms_crawler_meta')->where('target_id', '=', $crawlerId)->field('article_id')->select();
                    })->cache('article_preprocess_regexp_replace_' . $replaceField)->update($data);

                } else {
                    $articleIds = CrawlerMetaModel::where('target_id', '=', $crawlerId)->column('article_id');
                    $articles = ArticleModel::where('id', 'in', $articleIds)->select();

                    foreach ($articles as $key => $article) {
                        if ($replaceField == 'all') {
                            $article->title = preg_replace($searchText, $replaceText, $article['title']);
                            $article->keywords = preg_replace($searchText, $replaceText, $article['keywords']);
                            $article->description = preg_replace($searchText, $replaceText, $article['description']);
                            $article->content = preg_replace($searchText, $replaceText, $article['content']);
                            $article->save();
                        } else {
                            $article->$replaceField = preg_replace($searchText, $replaceText, $article[$replaceField]);
                            $article->save();
                        }
                    }
                }


                $this->success('数据正则替换成功！');
            }


        }


        $where = [
            ['status', '>=', ArticleModel::STATUS_CRAWLED],
            ['status', '<=', ArticleModel::STATUS_WAREHOUSED]
        ];

        if (empty($replaceField) or $replaceField == 'all') {
            $where[] = ['a.title|a.keywords|a.description|a.content', 'like', '%' . $searchText . '%'];
        } else {
            $where[] = ['a.' . $replaceField, 'like', "%$searchText%"];
        }

        $fields = ['a.id,a.title,a.status,a.post_time,a.create_time'];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
            'query' => input('param.')
        ];

        $ArticleModel = new ArticleModel();
        $query = $ArticleModel->where($where)->alias('a');
        if ($crawlerId > 0) {
            $query->join('cms_crawler_meta b', "a.id=b.article_id and b.target_id=$crawlerId");
        }

        $articleList = $query->field($fields)->order('id desc')->paginate(20, false, $pageConfig);
        $this->assign('articleList', $articleList);
        $this->assign('pages', $articleList->render());

        return $this->fetch('preprocess');
    }

    // 数据入库
    public function warehouse()
    {
        $crawlerId = input('crawlerId/d', -1);

        $crawlerModel = new CrawlerModel();
        $crawlerList = $crawlerModel->where('status', '>', CrawlerModel::STATUS_WAITING)->order('id desc')->select();
        $this->assign('crawlerList', $crawlerList);

        if (request()->isAjax()) {
            $aids = input('aids', '[]');
            if ($crawlerId < 0 && empty($aids)) {
                $this->error('请选择采集规则');
            }

            $aids = json_decode($aids, true);
            if (count($aids) > 0) {
                //$aids = CrawlerMetaModel::where('target_id', '=', $crawlerId)->column('article_id');

                $where = [
                    ['id', 'in', $aids],
                    ['status', '=', ArticleModel::STATUS_CRAWLED]
                ];
                $articles = ArticleModel::where($where)->field('id,status')->select();
                if (count($articles) == 0) {
                    $this->error('您选中的文章已入库，无需再入库!');
                }

                $count = 0;
                foreach ($articles as $key => $article) {
                    $article->status = ArticleModel::STATUS_WAREHOUSED;
                    $article->save();
                    $count++;
                }

                $this->success('成功入库'. $count . '篇文章');
            } else {
                $ArticleModel = new ArticleModel();
                $fields = ['a.id,a.title,a.status,a.create_time'];

                if ($crawlerId <= 0) {
                    $this->error('请选择采集规则');
                }

                //查找未入库文章
                $where = [
                    ['status', '=', ArticleModel::STATUS_CRAWLED],
                    ['target_id', '=', $crawlerId]
                ];
                $articles = $ArticleModel->where($where)->alias('a')->join('cms_crawler_meta b', 'a.id=b.article_id')->field($fields)->select();

                foreach ($articles as $key =>$article) {
                    $article->status = ArticleModel::STATUS_WAREHOUSED;
                    $article->save();
                }

                $this->success('成功入库'. count($articles) . '篇文章');
            }

        }


        //查找文章
        $where = [
            ['status', '=', ArticleModel::STATUS_CRAWLED]
        ];

        $fields = ['a.id,a.title,a.status,a.post_time,a.create_time'];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
            'query' => input('param.')
        ];

        $ArticleModel = new ArticleModel();
        $query = $ArticleModel->where($where)->alias('a');
        if ($crawlerId > 0) {
            $query->join('cms_crawler_meta b', "a.id=b.article_id and b.target_id=$crawlerId");
        }
        $articleList = $query->field($fields)->paginate(20, false, $pageConfig);

        $this->assign('articleList', $articleList);
        $this->assign('pages', $articleList->render());

        return $this->fetch('warehouse');
    }

    // 发布计划
    public function postPlan()
    {
        $crawlerModel = new CrawlerModel();
        $where = [
            ['status', 'in', [CrawlerModel::STATUS_CRAWLING, CrawlerModel::STATUS_CRAWL_SUCCESS]]
        ];
        $crawlerList = $crawlerModel->where($where)->order('id desc')->select();
        $this->assign('crawlerList', $crawlerList);

        $ArticleModel = new ArticleModel();
        $crawlerId = input('crawlerId/d', -1);

        if (request()->isAjax()) {
            $days = input('days/d', 0); // 执行天数
            $countPerDay = input('countPerDay/d', 0); //每天文章总数量
            $hourCounts = input('hourCounts/s', '[]'); //文章数组
            $hourCounts = json_decode($hourCounts, true);

            //查找已入库的文章
            $where = [
                ['status', '=', ArticleModel::STATUS_WAREHOUSED]
            ];

            //按天发布文章
            for ($i = 0; $i < $days; $i++) {
                $query = $ArticleModel->where($where)->alias('a');
                if ($crawlerId > 0) {
                    $query->join('cms_crawler_meta b', "a.id=b.article_id and b.target_id=$crawlerId");
                }
                $aids = $query->limit($countPerDay)->column('a.id');

                $date = date_create(date("Y-m-d", strtotime("+$i day"))); //设置当天的时间

                //时间数组
                foreach ($hourCounts as $hour => $count) {
                    if ($count === 0) {
                        continue;
                    }

                    //给每篇文章设置发布时间
                    for (; $count > 0; $count--) {
                        if (count($aids) == 0) {
                            break;
                        }

                        $aid = array_pop($aids);
                        $postTime = date_time_set($date, $hour, rand(0, 59), rand(0, 59)); //设定发布时间
                        //dump($postTime);continue;
                        $metaWhere = [
                            'article_id' => $aid,
                            'meta_key' => ArticleMetaModel::KEY_TIMING_POST
                        ];

                        $articleMeta = ArticleMetaModel::where($metaWhere)->find();
                        if ($articleMeta) {
                            $articleMeta->update_time = date_time();
                            $articleMeta->meta_value = $postTime->format('Y-m-d H:i:s');
                            $res = $articleMeta->save();
                        } else {
                            $data = array_merge($metaWhere, ['meta_value' => $postTime->format('Y-m-d H:i:s')]);
                            $data['update_time'] = date_time();
                            $data['create_time'] = date_time();
                            $res = ArticleMetaModel::create($data);
                        }

                        ArticleModel::update(['status' => ArticleModel::STATUS_DRAFT, 'id' => $aid], ['id' => $aid]);
                    }

                }
            }

            $this->success("设定成功！");
        }


        $where = [
            ['status', '=', ArticleModel::STATUS_WAREHOUSED]
        ];

        $fields = ['a.id,a.title,a.status,a.post_time,a.create_time'];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
            'query' => input('param.')
        ];

        $query = $ArticleModel->where($where)->alias('a');
        if ($crawlerId > 0) {
            $query->join('cms_crawler_meta b', "a.id=b.article_id and b.target_id=$crawlerId");
        }
        $articleList = $query->field($fields)->paginate(20, false, $pageConfig);

        $this->assign('articleList', $articleList);
        $this->assign('pages', $articleList->render());

        return $this->fetch('postPlan');
    }
}

