<?php
namespace app\cms\controller;

use app\common\model\ArticleMetaModel;
use app\common\model\ArticleModel;
use app\common\model\CategoryModel;
use app\common\model\CommentModel;
use think\helper\Time;

/**
 * 文章
 */
class Article extends Base
{
//    protected $beforeActionList = [
//        'getCategory'  =>  ['only' => 'index,viewArticle'],
//    ];
//
//    protected function getCategory()
//    {
//        $CategoryModel = new CategoryModel();
//        $cateList = $CategoryModel->where('status','=', 1)->order('sort')->select();
//        $this->assign('cateList', $cateList);
//    }

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 全部文章列表
     * route: /index.html
     * @return \think\response\View
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 文章列表，根据cid|cname栏目分类
     * @param $cid: 分类id
     * @param $cname: 分类名, $cid|$cname至少要有一个
     * @return \think\response\View
     * @throws \think\Exception
     * */
    public function articleList($cid=0, $cname='', $csubname='')
    {
        if (!empty($cname) && !empty($csubname)) {
            return $this->articleSublist($cname, $csubname);
        }

        if (empty($cid) && !empty($cname)) {
            $CategoryModel = new CategoryModel();
            $category = $CategoryModel->where(['title_en' => $cname])->find();
            if (empty($category)) {
                $this->error('分类名不存在');
            }

            $cid = $category['id'];
        }
        $this->assign('cid', $cid);
        $this->assign('cname', $cname);

        $CategoryModel = new CategoryModel();
        $category = $CategoryModel->where(['id' => $cid])->find();
        if (empty($category)) {
            $this->error('分类不存在');
        }
        $this->assign('category', $category);

        //核心数据的查询，使用标签操作

        return $this->fetch('list');
    }

    /**
     * 文章二级子列表，根据cname|csubname栏目分类
     * @param $cname: 分类名
     * @param $csubname: 子分类名
     * @return \think\response\View
     * @throws \think\Exception
     * */
    private function articleSublist($cname='', $csubname='')
    {
        if (empty($cname) || empty($csubname)) {
            $this->error('参数错误');
        }

        $CategoryModel = new CategoryModel();
        $child = $CategoryModel->where(['title_en' => $csubname])->find();
        if (empty($child)) {
            $this->error('子分类名不存在');
        }

        $category = $child['parent'];
        if (empty($category) || $category->title_en != $cname) {
            $this->error('两个分类不存在父子关系!');
        }

        $cid = $child['id'];
        $this->assign('cid', $cid);
        $this->assign('cname', $cname);
        $this->assign('csubname', $csubname);


        $this->assign('category', $category);
        $this->assign('subcategory', $child);

        //核心数据的查询，使用标签操作

        return $this->fetch('sublist');
    }

    /**
     * 文章详细内容
     * @param $aid
     * @param $cid: 分类id, 可不传，有传值时是用于定位来源分类;
     * @param $cname: 分类title_en, 可不传，有传值时是用于定位来源分类;
     * @param $page: _ueditor_page_break_tag_ 进行内容分页处理, 从0开始【待实现】
     * @return \think\response\View
     * @throws \think\Exception
     */
    public function viewArticle($aid=0, $cid=0, $cname='', $page=1)
    {
        if (empty($aid)) {
            $this->error('参数错误');
        }

        $ArticleModel = new ArticleModel();
        $article = $ArticleModel->find($aid);
        if (empty($article) || $article['status'] != ArticleModel::STATUS_PUBLISHED) {
            $this->error('文章不存在');
        }
        $this->assign('aid', $aid);

        //阅读量+1(一个ip一天只能添加1的浏览量),但阅读记录保持入库
        $id = $article['id'];
        $ip = \think\facade\Request::ip(0, true);
        $today = Time::today();
        $where = [
            ['article_id', '=', $id],
            ['meta_key', '=', 'read_ip'],
            ['meta_value', '=', $ip],
            ['create_time', '>=', date_time($today[0])],
            ['create_time', '<', date_time($today[1])]
        ];

        $ArticleMetaModel = new ArticleMetaModel();
        $meta = $ArticleMetaModel->where($where)->find();
        if (!$meta) {
            $ArticleModel->where('id', $aid)->setInc('read_count');
        }

        $data = [
            'article_id' => $id,
            'meta_key' => 'read_ip',
            'meta_value' => $ip,
            'update_time' => date_time(),
            'create_time' => date_time()
        ];
        $ArticleMetaModel->insert($data);


        if (empty($cid)) {
            $categorys = $article->categorys;
            $cid = $categorys[0]->id;
        }
        $this->assign('cid', $cid);

        $CategoryModel = new CategoryModel();
        $category = $CategoryModel->where(['id' => $cid])->find();
        if (empty($category)) {
            $this->error('分类不存在');
        }
        $this->assign('category', $category);

        //评论
        $CommentModel = new CommentModel();
        $list = $CommentModel->where(['article_id' => $aid, 'status' => CommentModel::STATUS_PUBLISHED])->order('create_time desc')->paginate(6,false, ['query'=>input('param.')]);
        $page = $list->render();
        $this->assign('comments', $list);
        $this->assign('page', $page);

        return view('viewArticle');
    }

    /**
     * 根据标签值，查询相关文章
     * @param string $tag 文章标签
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function tag($tag='')
    {
        if (empty($tag)) {
            $this->error('标签不能为空');
        }
        $where = [
            'status' => ArticleModel::STATUS_PUBLISHED,
            'meta.meta_value' => $tag
        ];

        $fields = 'article.id,title,description,keywords,author,thumb_image_id,post_time,article.update_time,article.create_time,is_top,status,read_count,sort,ad_id';
        $orders = [
            'post_time' => 'desc',
            'update_time' => 'desc'
        ];
        $listRow = input('param.list_rows/d') ? input('param.list_rows/d') : 20;
        $pageConfig = [
            'query' => input('param.')
        ];

        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->alias('article')->leftJoin('cms_article_meta meta', 'article.id = meta.article_id')->where($where)->field($fields)->order($orders)->paginate($listRow,false, $pageConfig);

        $this->assign('list', $list);
        $this->assign('tag', $tag);

        return $this->fetch('tag');
    }


}
