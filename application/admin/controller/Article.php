<?php
namespace app\admin\controller;

use app\common\model\AdAdtypeModel;
use app\common\model\AdModel;
use app\common\model\AdtypeModel;
use app\common\model\ArticleMetaModel;
use app\common\model\CommentModel;
use app\common\model\MessageModel;
use app\common\model\UserModel;
use app\common\model\ArticleModel;
use app\common\model\CategoryModel;

/**
* 文章控制器
*/
class Article extends Base
{
    //文章列表
    public function index()
    {
        $articleModel = new ArticleModel();

        $categoryId = input('param.categoryId/d');
        $map[] = ['status', '>=', 0]; //状态
        if ($categoryId > 0) {
            $childs = CategoryModel::getChild($categoryId);
            $childCateIds = $childs['ids'];
            array_push($childCateIds, $categoryId);
            $articleModel = ArticleModel::has('CategoryArticle', [['category_id','in',$childCateIds]]);
        }

        $key = input('param.key');
        if (!empty($key)) {
            $map[] = ['title', 'like',"%{$key}%"];
        }

        //文章状态
        $status = input('param.status','');
        if ($status !== '') {
            $map[] = ['status', '=', $status];
        }

        $startTime = input('param.startTime', '');
        $endTime = input('param.endTime', '');
        if (!empty($endTime)) {
            $map[] = ['create_time', '<=', $endTime . ' 23:59:59'];
        }
        if (!empty($startTime)) {
            $map[] = ['create_time', '>=', $startTime . ' 00:00:00'];
        }

        $fields = 'id,title,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,ad_id';
        $orders = [
            'is_top' => 'desc',
            'post_time' => 'desc',
            'update_time' => 'desc'
        ];

        $sortedFields = ['post_time' => '', 'create_time' => ''];
        $field =input('field');
        $sort = input('sort');
        if ($field && $sort) {
            unset($orders['update_time']);
            unset($orders['post_time']);
            $orders = array_merge($orders, [$field => $sort]);
            $sortedFields[$field] = $sort;
        }

        $listRow = input('param.list_rows/d') ? input('param.list_rows/d') : 20;
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
            'query' => input('param.')
        ];
        $list = $articleModel->where($map)->field($fields)->order($orders)->distinct('id')->paginate($listRow,false, $pageConfig);

        $this->assign('list', $list);
        $this->assign('pages', $list->render());
        $this->assign('sortedFields', $sortedFields);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //文章分类列表
        $CategoryModel = new CategoryModel();
        $cateList = $CategoryModel->getTreeData('tree','sort,id', 'title_cn');
        $this->assign('categoryList', $cateList);

        return $this->fetch('article/index');
    }

    //新增文章
    public function addArticle()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $data['content'] = remove_xss($data['content']);

            $check = validate('Article')->scene('add')->check($data);
            if ($check !== true) {
                $this->error(validate('Article')->getError());
            }

            //审核开关关闭时
            if ($data['status'] == ArticleModel::STATUS_PUBLISHING && get_config('article_audit_switch') === 'false') {
                $data['status'] = ArticleModel::STATUS_PUBLISHED;
            }
            $data['user_id'] = session('uid');
            $articleModel = new ArticleModel();
            $res = $articleModel->add($data);

            if ($res) {
                $this->success('新增成功', url('Article/index'));
            } else {
                $this->error('新增失败:' . $articleModel->getError());
            }
        }

        //分类列表
        $CategoryModel = new CategoryModel();
        $categoryList = $CategoryModel->getTreeData('tree','sort,id', 'title_cn');
        $this->assign('categoryList', $categoryList);

        return $this->fetch('article/addArticle');
    }

    //编辑文章
    public function editArticle($id)
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $data['content'] = remove_xss($data['content']);

            //审核开关关闭时
            if ($data['status'] == ArticleModel::STATUS_PUBLISHING && get_config('article_audit_switch') === 'false') {
                $data['status'] = ArticleModel::STATUS_PUBLISHED;
            }
            $ArticleModel = new ArticleModel();
            $res = $ArticleModel->edit($data);

            if ($res) {
                $this->success('更新成功', url('Article/index'));
            } else {
                $this->error('更新失败:' . $ArticleModel->getError());
            }
        }

        $article = ArticleModel::get(['id'=>$id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }
        $this->assign('article', $article);

        //文章分类id
        $categoryList = $article->categorys;
        $oldCategoryIds = [];
        foreach ($categoryList as $cate) {
            $oldCategoryIds[] = $cate['id'];
        }
        $this->assign('oldCategoryIds', $oldCategoryIds);

        //分类列表
        $CategoryModel = new CategoryModel();
        $categoryList = $CategoryModel->getTreeData('tree','sort,id', 'title_cn');
        $this->assign('categoryList', $categoryList);

        return $this->fetch('article/addArticle');
    }

    //查看文章
    public function viewArticle($id)
    {
        $article = ArticleModel::get(['id'=>$id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }
        $this->assign('article', $article);

        $CommentModel = new CommentModel();

        $where = [
            'article_id' => $id,
            'status' => CommentModel::STATUS_PUBLISHED
        ];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
        ];
        $comments = $CommentModel->where($where)->order('create_time desc')->paginate(6, false, $pageConfig);
        $this->assign('comments', $comments);
        $this->assign('id', $id);

        //检测索引
        $jobHandlerClass  = 'app\admin\job\Webmaster@checkIndex';
        $jobData = [
            'id' => $id,
            'url' => url('cms/Article/viewArticle', ['aid' => $id], true, get_config('domain_name')),
        ];
        $jobQueue = config('queue.default');
        \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);

        return $this->fetch('article/viewArticle');
    }

    //删除文章
    public function deleteArticle($id)
    {
        $article = ArticleModel::get(['id'=>$id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }
        $article->status = ArticleModel::STATUS_DELETED;
        $res = $article->save();
        if ($res) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败');
        }
    }

    //设定定时发布
    public function setTimingPost()
    {
        $id = input('id/s', 0);
        $postTime = input('postTime/s', '');

        $ids = [];
        if (is_int($id)) {
            $article = ArticleModel::get(['id' => $id]);
            if (empty($article)) {
                $this->error('文章不存在');
            }

            $ids[] = $id;
        } else {
            $ids = explode(',', $id);
        }

        $numRows = 0;
        foreach ($ids as $id) {
            $where = [
                'article_id' => $id,
                'meta_key' => ArticleMetaModel::KEY_TIMING_POST,
            ];
            $data = [
                'article_id' => $id,
                'meta_key' => ArticleMetaModel::KEY_TIMING_POST,
                'meta_value' => $postTime
            ];

            $ArticleMetaModel = new ArticleMetaModel();
            $meta = $ArticleMetaModel->where($where)->find(); //$ArticleMetaModel->find($where) 这种写法要求$where是主键值
            if ($meta) {
                $data['update_time'] = date_time();
                $res = $ArticleMetaModel->isUpdate(true)->save($data, ['id' => $meta->id]);
                $numRows++;
            } else {
                $data['update_time'] = date_time();
                $data['create_time'] = date_time();
                $res = ArticleMetaModel::create($data);
                $numRows++;
            }
        }

        if ($numRows > 0) {
            $this->success('设置成功');
        } else {
            $this->error('设置失败');
        }
    }

    //发布文章
    public function postArticle($id)
    {
        $article = ArticleModel::get($id);
        if (empty($article)) {
            $this->error('文章不存在');
        }

        $data = [
            'status' => ArticleModel::STATUS_PUBLISHING,
            'post_time' => date_time()
        ];
        //审核开关关闭时
        if (get_config('article_audit_switch') === 'false') {
            $data['status'] = ArticleModel::STATUS_PUBLISHED;
        }

        $res = $article->isUpdate(true)->save($data, ['id' => $id]);
        if ($res) {
            $this->success('成功发布');
        } else {
            $this->error('发布失败');
        }
    }

    //文章初审
    public function auditFirst($id = 0, $pass = 1)
    {
        $article = ArticleModel::get(['id'=>$id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }

        if ($article->status != ArticleModel::STATUS_PUBLISHING) {
            $this->error('文章状态不正确，无法进行初审');
        }

        if ($pass) {
            $article->status = ArticleModel::STATUS_FIRST_AUDITED;
        } else {
            $article->status = ArticleModel::STATUS_FIRST_AUDIT_REJECT;
        }

        $res = $article->save();
        if ($res !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //文章终审
    public function auditSecond($id = 0, $pass = 1)
    {
        $article = ArticleModel::get(['id'=>$id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }

        if ($article->status != ArticleModel::STATUS_FIRST_AUDITED) {
            $this->error('文章状态未初审通过，无法进行终审');
        }

        if ($pass) {
            $article->status = ArticleModel::STATUS_PUBLISHED;
        } else {
            $article->status = ArticleModel::STATUS_SECOND_AUDIT_REJECT;
        }

        $res = $article->save();
        if ($res !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //上头条
    public function upTop()
    {
        $data = input('post.');
        $rule = [
            'image_id|头条图片' => 'require|number',
            'title|标题' => 'require',
        ];
        $check = $this->validate($data,$rule);
        if ($check !== true) {
            $this->error($check);
        }

        $data['type'] = AdtypeModel::TYPE_BANNER_HEADLINE;
        $data['create_time'] = date_time();
        $AdModel = new AdModel();
        $res = $AdModel->allowField(true)->save($data);
        if ($res) {
            $ArticleModel = new ArticleModel();
            $ArticleModel->where('id', $data['artId'])->setField('ad_id', $AdModel->id);
            $this->success('成功新增头条');
        } else {
            $this->error('新增失败');
        }
    }

    //取消头条
    public function deleteTop()
    {
        $adId = input('adId/d', 0);
        $artId = input('artId/d', 0);
        if (empty($adId) || empty($artId)) {
            $this->error('参数错误');
        }
        $res = AdModel::destroy($adId);
        $ArticleModel = new ArticleModel();
        $res = $ArticleModel->where('id', $artId)->setField('ad_id', 0);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //置顶文章
    public function setTop()
    {
        $artId = input('param.id/d');
        $article = ArticleModel::get(['id'=>$artId]);
        $article->is_top = 1;
        $res = $article->save();
        if ($res) {
            $this->success('成功置顶');
        } else {
            $this->error('置顶失败');
        }
    }

    //取消置顶文章
    public function unsetTop()
    {
        $artId = input('param.id/d');
        $article = ArticleModel::get(['id'=>$artId]);
        $article->is_top = 0;
        $res = $article->save();
        if ($res) {
            $this->success('成功取消置顶');
        } else {
            $this->error('取消置顶失败');
        }
    }

    //评论列表
    public function commentList()
    {
        $CommentModel = new CommentModel();

        $fields = 'id, content, article_id, create_time, status, author, ip, pid';
        $list = $CommentModel->field($fields)->order('create_time desc')->distinct('id')->paginate(6, false);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        $MessageModel = new MessageModel();
        $data['status'] = MessageModel::STATUS_READ;
        $data['read_time'] = date_time();
        $data['is_readed'] = 1;//0未读，1已读
        $MessageModel->save($data, ['type' => MessageModel::TYPE_COMMENT]);

        return $this->fetch('commentList');
    }

    //审核评论
    public function auditComment($id=0, $pass=1)
    {
        $com = CommentModel::get(['id'=>$id]);
        if (empty($com)) {
            $this->error('评论不存在');
        }

        if ($com->status != CommentModel::STATUS_DRAFT) {
            $this->error('评论审核未通过，无法发布');
        }

        if ($pass) {
            $com->status = CommentModel::STATUS_PUBLISHED;
        } else {
            $com->status = CommentModel::STATUS_REFUSE;
        }

        $res = $com->save();
        if ($res !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }

    }

    //回复评论
    public function postComment()
    {
        if (request()->isAjax()) {
            $aid = input('article_id/d', 0);
            $pid = input('pid/d', 0);
            $content = input('content/s', '');

            $data = [];
            if (session('uid')) {
                $uid = session('uid');

                $user = UserModel::get($uid);
                $author = $user->nickname;
                $data['uid'] = $uid;
                $data['author'] = $author;
            } else {
                $author = session('visitor');
                $data['author'] = $author;
            }

            $data['create_time'] = date_time();
            $data['ip'] = request()->ip(0, true);
            $data['article_id'] = $aid;
            $data['content'] = $content;
            $data['pid'] = $pid;
            $CommentModel = new CommentModel();
            $result = $CommentModel->save($data);
            if (!$result) {
                $this->error('回复失败');
            } else {
                $this->success('回复成功', url('Article/commentList'));
            }
        }

        return $this->fetch('commentList');
    }

    //删除评论
    public function deleteComment($id)
    {
        $CommentModel = new CommentModel();

        $res = $CommentModel->where('id', $id)->delete();
        if ($res) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败');
        }
    }

    //文章分类
    public function categoryList()
    {
        $CategoryModel = new CategoryModel();
        $list = $CategoryModel->getTreeData('tree','sort,id', 'title_cn', 'id', 'pid');
        $this->assign('list', $list);

        return $this->fetch('categoryList');
    }

    //新增分类
    public function addCategory()
    {
        //数据处理
        if (request()->isAjax()) {
            $data = input('post.');
            $CategoryModel = new CategoryModel();
            if (empty($data['id'])) {
                $res = $CategoryModel->isUpdate(false)->save($data);
            } else {
                $res = $CategoryModel->isUpdate(true)->save($data);
            }

            if ($res) {
                $this->success('操作成功',url('Article/categoryList'));
            } else {
                $this->error('操作失败');
            }
        }

        return $this->fetch('addCategory');
    }

    //分类排序
    public function orderCategory()
    {
        $data = input('post.');
        $arr = [];
        foreach ($data as $k => $v) {
            $arr[] = [
                'id' => $k,
                'sort' => empty($v) ? 0 : $v
            ];
        }
        $CategoryModel = new CategoryModel();
        $result = $CategoryModel->isUpdate(true)->saveAll($arr);
        if ($result) {
            $this->success('排序成功',url('Article/categoryList'));
        }else{
            $this->error('排序失败');
        }
    }

    //编辑分类
    public function editCategory($id)
    {
        $CategoryModel = new CategoryModel();
        $category = $CategoryModel->find($id);
        if (empty($category)) {
            $this->error('数据不存在');
        }
        $this->assign('category', $category);
        return $this->fetch('addCategory');
    }

    //删除分类
    public function deleteCategory($id)
    {
        $CategoryModel = new CategoryModel();
        $res = $CategoryModel->where('id', $id)->delete();
        if ($res) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败');
        }
    }

    //广告内链列表
    public function adList()
    {
        $title = input('param.title', '');
        $type = input('param.type', '');

        $where = [];
        if (!empty($title)) {
            $where[] = ['title', 'like', "%$title%"];
        }
        if (!empty($type)) {
            $AdAdtypeModel = new AdAdtypeModel();
            $adIds = $AdAdtypeModel->where('type', $type)->field('distinct ad_id')->column('ad_id');//column变成一维数组
            $where[] = ['id','in', $adIds];
        }
        $AdModel = new AdModel();
        $list = $AdModel->where($where)->order('sort,create_time desc')->paginate(10, false, ['query'=>input('param.')]);
        $this->assign('list',$list);
        $this->assign('pages', $list->render());

        //类型列表
        $AdtypeModel = new AdtypeModel();
        $typeList = $AdtypeModel->order('type asc')->field('type, title_cn')->select();
        $this->assign('typeList', $typeList);

        return view('adList');
    }

    //新增广告内链
    public function addAd()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $rule = [
                'title|标题' => 'require',
                'url' => ['require'],
                'types' => ['require'],
                //'image_id|专题图片' => 'require|number',
            ];
            $check = $this->validate($data, $rule);
            if ($check !== true) {
                $this->error($check);
            }

            $data['type'] = 4;
            $data['create_time'] = date_time();

            $AdModel = new AdModel();
            $rowsNum = $AdModel->isUpdate(false)->allowField(true)->save($data);
            //新增中间表数据
            $AdModel->adtypes()->saveAll($data['types']);

            if ($rowsNum !== false) {
                $this->success('成功新增广告', url('article/adList'));
            } else {
                $this->error('新增失败');
            }
        }

        //类型列表
        $AdtypeModel = new AdtypeModel();
        $typeList = $AdtypeModel->order('type asc')->field('type,title_cn,title_en,image_size')->select();
        $this->assign('typeList', $typeList);

        return view('addAd');
    }

    //修改广告内链
    public function editAd($adId = 0)
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $rule = [
                'id' => ['require'],
                'title|标题' => 'require',
                'url' => ['require'],
                'types' => ['require'],
                //'image_id|专题图片' => 'require|number',
            ];
            $check = $this->validate($data,$rule);
            if ($check !== true) {
                $this->error($check);
            }

            $data['create_time'] = date_time();
            $id = $data['id'];
            $AdModel = new AdModel();
            $rowsNum = $AdModel->isUpdate(true)->allowField(true)->save($data, ['id'=>$id]);
            //修改中间表数据
            if (!empty($data['types'])) {
                $AdModel->adtypes()->detach();
                $AdModel->adtypes()->saveAll($data['types']);
            }
            if ($rowsNum !== false) {
                $this->success('成功修改广告', url('article/adList'));
            } else {
                $this->error('修改失败');
            }
        }

        $ad = AdModel::get(['id'=>$adId]);
        if (empty($ad)) {
            $this->error('广告内链不存在');
        }
        $this->assign('ad', $ad);

        //old types
        $relationTypes = $ad->adtypes;
        $oldTypes = [];
        foreach ($relationTypes as $adtype) {
            $oldTypes[] = $adtype['type'];
        }
        $this->assign('oldTypes', $oldTypes);

        //类型列表
        $AdtypeModel = new AdtypeModel();
        $typeList = $AdtypeModel->order('type asc')->field('type,title_cn,title_en,image_size')->select();
        $this->assign('typeList', $typeList);

        return $this->fetch('article/addAd');
    }

    //删除广告内链
    public function deleteAd($adId = 0)
    {
        $res = AdModel::destroy($adId);
        if ($res) {
            $ArticleModel = new ArticleModel();
            $ArticleModel->where('ad_id',$adId)->setField('ad_id',0); //头条文章取消头条

            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //广告排序
    public function orderAd()
    {
        $data = input('post.');
        $AdModel = new AdModel();
        foreach ($data as $k => $v) {
            $AdModel->where('id', $k)->setField('sort', $v);
        }
        $this->success('成功排序');
    }

    //文章访问统计
    public function articleStat($id)
    {
        $article = ArticleModel::get(['id' => $id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }
        $this->assign('article', $article);

        $startTime = input('param.startTime');
        $endTime = input('param.endTime');
        if (!(isset($startTime) && isset($endTime))) {
            $startTime  = date('Y-m-d',strtotime('-7 day'));
            $endTime   = date('Y-m-d');
        }

        $startDatetime = date('Y-m-d 00:00:00', strtotime($startTime));
        $endDatetime = date('Y-m-d 23:59:59', strtotime($endTime));

        $where = [
            ['update_time', 'between', [$startDatetime, $endDatetime]]
        ];

        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
        ];

        $ArticleMetaModel = new ArticleMetaModel();
        $list = $ArticleMetaModel->where(['article_id' => $id, 'meta_key' => 'read_ip'])->where($where)->order('update_time desc')->paginate(15, false, $pageConfig);
        $startTimestamp = strtotime($startTime);
        $endTimestamp = strtotime($endTime);

        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('startTimestamp', $startTimestamp);
        $this->assign('id', $id);
        $this->assign('endTimestamp', $endTimestamp);
        $this->assign('count', $list->count());
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        return $this->fetch('article/articleStat');

    }

    //文章访问统计图
    public function echartShow($id)
    {
        $article = ArticleModel::get(['id' => $id]);
        if (empty($article)) {
            $this->error('文章不存在');
        }

        $option =[
            'xAxis'=> ['data'=>[]],
            'series'=> [['data'=>[]]],
        ];

        $where = [];
        $startTime = input('param.start');
        $endTime = input('param.end');

        $ArticleMetaModel = new ArticleMetaModel();
        for ($i = $startTime ; $i <= $endTime; $i += (24*3600)) {
            $day = date('m-d',$i);
            $beginTime = mktime(0, 0, 0, date('m',$i), date('d',$i), date('Y',$i));
            $endTime = mktime(23, 59, 59, date('m',$i), date('d',$i), date('Y',$i));

            unset($where);
            $where[] = ['update_time','between', [date_time($beginTime), date_time($endTime)]];
            $inquiryCount = $ArticleMetaModel->where(['article_id' => $id, 'meta_key' => 'read_ip'])->where($where)->count();

            array_push($option['xAxis']['data'], $day);
            array_push($option['series'][0]['data'], $inquiryCount);
        }
        $this->success('success', '', $option);
    }
}
