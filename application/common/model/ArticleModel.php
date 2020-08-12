<?php
namespace app\common\model;

use think\facade\Log;
use think\facade\Cache;
use app\common\exception\ModelException;

class ArticleModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'article';

    const STATUS_DELETED = -1;//删除
    const STATUS_DRAFT = 0; //草稿
    const STATUS_PUBLISHING = 1; //申请发布
    const STATUS_FIRST_AUDIT_REJECT = 2; //初审拒绝
    const STATUS_FIRST_AUDITED = 3; //初审通过
    const STATUS_SECOND_AUDIT_REJECT = 4; //终审拒绝
    const STATUS_PUBLISHED = 5; //已发布

    const STATUS_CRAWLED = -5; //已抓取
    const STATUS_WAREHOUSED = -4; //已入库

    protected $pk = 'id';

    protected $auto = ['update_time'];
    protected $insert = ['status','create_time','uid'];
    protected $update = ['update_time'];

    //静态初始化时，依赖注入事件
    public static function init()
    {
        //计算文章相似度，article_a_id > article_b_id
        self::event('after_insert', function($article) {
            $id = $article->id;

            //指定任务的处理类，若指定至方法时，@methodName
            $jobHandlerClass  = 'app\admin\job\Article@afterInsert';
            //任务的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串; jobData 为对象时，存储其public属性的键值对
            $jobData = ['id' => $id, 'create_time' => date_time()];
            //任务归属的队列名称，如果为新队列，会自动创建
            $jobQueue = config('queue.default');

            $isPushed = \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);
            // database 驱动时，返回值为 1|false; redis 驱动时，返回值为 随机字符串|false
            if ($isPushed !== false) {
                Log::info('文章相似度LCS更新Job入列成功...');
            } else {
                Log::info('文章相似度LCS更新入列失败！');
            }

            //若文章已发布，提交链接|检测收录，$article['status'] == ArticleModel::STATUS_PUBLISHED 此时做这个判断会有延迟

            $articleUrl = get_config('domain_name') . url('cms/Article/viewArticle', ['aid' => $id], true, false);

            //提交链接
            if ($article['status'] == ArticleModel::STATUS_PUBLISHED) {
                $jobHandlerClass = 'app\admin\job\Webmaster@pushLinks';
                $jobData = ['id' => $id, 'url' => $articleUrl, 'create_time' => date_time()];
                $jobQueue = config('queue.default');
                \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);
            }

            //检测收录,延迟4,6,24小时
            $jobHandlerClass  = 'app\admin\job\Webmaster@checkIndex';
            $jobData = ['id' => $id, 'url' => $articleUrl, 'create_time' => date_time()];
            $jobQueue = config('queue.default');
            \think\Queue::later(2 * 60 * 60, $jobHandlerClass, $jobData, $jobQueue);
            \think\Queue::later(4 * 60 * 60, $jobHandlerClass, $jobData, $jobQueue);
        });
        self::event('after_update', function($article) {
            $id = $article->id;

            //指定任务的处理类，若指定至方法时，@methodName
            $jobHandlerClass  = 'app\admin\job\Article@afterUpdate';
            //任务的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串; jobData 为对象时，存储其public属性的键值对
            $jobData = ['id' => $id, 'create_time' => date_time()];
            //任务归属的队列名称，如果为新队列，会自动创建
            $jobQueue = config('queue.default');

            $isPushed = \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);
            // database 驱动时，返回值为 1|false; redis 驱动时，返回值为 随机字符串|false
            if ($isPushed !== false) {
                Log::info('文章相似度LCS更新Job入列成功...');
            } else {
                Log::info('文章相似度LCS更新入列失败！');
            }

            //若文章已发布，提交链接|检测收录，$article['status'] == ArticleModel::STATUS_PUBLISHED 此时做这个判断会有延迟

            //提交链接
            if (!isset($article->status)) {
                Log::info('article -> status 未有值 isset false!');
                $article = ArticleModel::where(['id' => $id])->field('id,status')->find();
            }
            if ($article->status == ArticleModel::STATUS_PUBLISHED) {
                $jobHandlerClass  = 'app\admin\job\Webmaster@pushLinks';
                $articleUrl = get_config('domain_name') . url('cms/Article/viewArticle', ['aid' => $id], true, false);
                $jobData = ['id' => $id, 'url' => $articleUrl, 'create_time' => date_time()];
                $jobQueue = config('queue.default');
                \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);

                //检测收录,延迟4,6,24小时
                $jobHandlerClass  = 'app\admin\job\Webmaster@checkIndex';
                $jobData = ['id' => $id, 'url' => $articleUrl, 'create_time' => date_time()];
                $jobQueue = config('queue.default');
                \think\Queue::later(2 * 60 * 60, $jobHandlerClass, $jobData, $jobQueue);
                \think\Queue::later(4 * 60 * 60, $jobHandlerClass, $jobData, $jobQueue);
            } else {
                Log::info('上次更新无需提交链接，状态值 为:' . $article->status_text);
            }

        });
    }

    //属性：status_text
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            -1 => '删除',
            0 => '草稿',
            1 => '申请发布',
            2 => '初审拒绝',
            3 => '初审通过',
            4 => '终审拒绝',
            5 => '已发布',
            -4 => '已入库',
            -5 => '已抓取',
        ];
        return isset($status[$data['status']])?$status[$data['status']] : '未知';
    }

    //属性: timing 定时
    public function getTimingAttr($value, $data)
    {
        $id = $data['id'];
        $ArticleMetaModel = new ArticleMetaModel();
        $articleMeta = $ArticleMetaModel->getMeta($id, ArticleMetaModel::KEY_TIMING_POST);
        return $articleMeta ? $articleMeta['meta_value'] : '0';
    }

    //关联表：缩略图
    public function thumbImage()
    {
        return $this->hasOne('ImageModel', 'image_id', 'thumb_image_id');
    }

    //关联表：文章分类
    public function categorys()
    {
        return $this->belongsToMany('CategoryModel', config('database.prefix'). CMS_PREFIX . 'category_article', 'category_id', 'article_id');
    }
    //关联表：中间表，用于获取中间表数据，或查询has/hasWhere
    protected function categoryArticle()
    {
        return $this->hasMany('CategoryArticleModel','article_id','id');
    }

    //关联表：用户
    protected function user()
    {
        return $this->belongsTo('UserModel', 'uid');
    }

    //关联表：评论
    protected function comments()
    {
        return $this->hasMany('CommentModel', 'article_id');
    }

    //关联表crawler_meta表
    public function crawlerMeta()
    {
        return $this->hasOne('CrawlerMetaModel', 'article_id');
    }

    //新增文章
    public function add($data = [])
    {
        $data = $data?:input('post.');

        $validator = new \app\common\validate\Article();
        $check = $validator->scene('add')->check($data);
        if ($check !== true) {
            throw new ModelException(0, $validator->getError());
        }

        if ($data['status'] == ArticleModel::STATUS_PUBLISHING || $data['status'] == ArticleModel::STATUS_PUBLISHED) {
            $data['post_time'] = date_time();
        }

        $res = $this->allowField(true)->isUpdate(false)->save($data);

        if (!$res) {
            return false;
        }

        //分类，新增中间表数据
        $this->categorys()->saveAll($data['category_ids']);

        //标签,添加至meta表
        if (!empty($data['tags'])) {
            $tags = explode(',', $data['tags']);
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $this->meta(ArticleMetaModel::KEY_TAG, $tag, BaseModel::MODE_MULTIPLE_VALUE);
                }
            }
        }

        return true;
    }

    //修改文章
    public function edit($data = [])
    {
        $data = $data?:input('post.');
        $art = self::get(['id'=>$data['id']]);
        if (empty($art)) {
            throw new ModelException(0, '文章不存在');
        }

        if ($art->status == ArticleModel::STATUS_DRAFT && $data['status'] == ArticleModel::STATUS_PUBLISHED) {
            //审核开关关闭时
            if (get_config('article_audit_switch') === 'true' ) {
                $data['status'] = ArticleModel::STATUS_PUBLISHING;
            }
            if (empty($art->post_time)) {
                $data['post_time'] = date_time();//设置发布时间
            }
        }

        $validate = validate('Article');
        $check = $validate->scene('edit')->check($data);
        if ($check !== true) {
            throw new ModelException(0, $validate->getError());
            return false;
        }

        $res = $this->allowField(true)->isUpdate(true)->save($data);

        // 删除中间表数据
        if (!empty($data['category_ids'])) {
            $art->categorys()->detach();
            $art->categorys()->saveAll($data['category_ids']);
        }

        //标签,添加至meta表
        $this->meta(ArticleMetaModel::KEY_TAG, null, BaseModel::MODE_MULTIPLE_VALUE);
        if (!empty($data['tags'])) {
            $tags = explode(',', $data['tags']);
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $this->meta(ArticleMetaModel::KEY_TAG, $tag, BaseModel::MODE_MULTIPLE_VALUE);
                }
            }
        }

        return true;
    }

    //删除文章缓存
    public static function clearArticleCache($cateIds)
    {
        foreach ($cateIds as $cateId) {
            if ($cateId instanceof \think\Model) {
                $cateId = $cateId['id'];
            }
            $cate = CategoryModel::getParent1($cateId);
            foreach ($cate['ids'] as $k => $v) {
                if (Cache::has('login_article_cate_' . $v)) {
                    Cache::rm('login_article_cate_' . $v);
                }
                if (Cache::has('logout_article_cate_' . $v)) {
                    Cache::rm('logout_article_cate_' . $v);
                }
            }
        }
    }
}

?>