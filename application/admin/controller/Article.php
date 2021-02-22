<?php
namespace app\admin\controller;

use app\common\model\AdServingModel;
use app\common\model\AdModel;
use app\common\model\AdSlotModel;
use app\common\model\ArticleMetaModel;
use app\common\model\CommentModel;
use app\common\model\MessageModel;
use app\common\model\UserModel;
use app\common\model\ArticleModel;
use app\common\model\CategoryModel;
use think\facade\Cookie;

/**
* 文章控制器
*/
class Article extends Base
{
    //文章列表
    public function index()
    {
        $ArticleModel = new ArticleModel();

        $categoryId = input('param.categoryId/d');
        $where[] = ['status', '>=', ArticleModel::STATUS_DRAFT]; //状态

        $key = input('param.key');
        if (!empty($key)) {
            $where[] = ['title', 'like', "%{$key}%"];
        }

        $fields = 'id,title,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,ad_id';
        if ($categoryId > 0) {
            $childs = CategoryModel::getChild($categoryId);
            $childCateIds = $childs['ids'];
            array_push($childCateIds, $categoryId);

            //$ArticleModel = ArticleModel::has('CategoryArticle', [['category_id','in',$childCateIds]]);
            $fields = 'ArticleModel.id,title,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,ad_id';
            $ArticleModel = ArticleModel::hasWhere('CategoryArticle', [['category_id','in',$childCateIds]], $fields)->group([]); //hack:group用于清理hasmany默认加group key
        }

        //文章状态
        $status = input('param.status','');
        if ($status !== '') {
            $where[] = ['status', '=', $status];
        }

        $startTime = input('param.startTime', '');
        $endTime = input('param.endTime', '');
        //默认是按post_time查询，查询草稿时按create_time查询
        $queryTimeField = ($status == '' || $status == ArticleModel::STATUS_PUBLISHED) ? 'post_time' : 'create_time';
        if (!empty($endTime)) {
            $where[] = [$queryTimeField, '<=', $endTime . ' 23:59:59'];
        }
        if (!empty($startTime)) {
            $where[] = [$queryTimeField, '>=', $startTime . ' 00:00:00'];
        }


        $orders = [
            'sort' => 'desc',
            'post_time' => 'desc',
            //'update_time' => 'desc'  post_time已经可以确保排序固定了，因为post_time基本不重复
        ];

        $sortedFields = ['post_time' => '', 'create_time' => ''];
        $field =input('field');
        $sort = input('sort');
        if ($field && $sort) {
            unset($orders['sort']);
            unset($orders['post_time']);
            unset($orders['update_time']);
            $orders = array_merge($orders, [$field => $sort]);
            $sortedFields[$field] = $sort;
        }

        $listRow = input('param.list_rows/d') ? input('param.list_rows/d') : 20;
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
            'query' => input('param.')
        ];
        $list = $ArticleModel->where($where)->field($fields)->order($orders)->paginate($listRow, false, $pageConfig);

        $this->assign('list', $list);
        $this->assign('pages', $list->render());
        $this->assign('sortedFields', $sortedFields);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //文章分类列表
        $CategoryModel = new CategoryModel();
        $cateList = $CategoryModel->getTreeData('tree', 'sort,id', 'title_cn');
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
            $data['uid'] = session('uid');
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
                $url = Cookie::get('HTTP_REFERER');
                Cookie::delete('HTTP_REFERER');

                $this->success('更新成功', $url);
            } else {
                $this->error('更新失败:' . $ArticleModel->getError());
            }
        }

        $article = ArticleModel::get($id);
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

        //记录上一级来源，方便回跳
        $fromReferee = $this->request->server('HTTP_REFERER');
        $url = !empty($fromReferee) ? $fromReferee : url('Article/index');
        Cookie::set('HTTP_REFERER', $url);

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
        $comments = $CommentModel->where($where)->order('id desc')->paginate(6, false, $pageConfig);
        $this->assign('comments', $comments);
        $this->assign('id', $id);

        //检测索引
        $jobHandlerClass  = 'app\admin\job\Webmaster@checkIndex';
        $jobData = [
            'id' => $id,
            'url' => url('cms/Article/viewArticle', ['aid' => $id], true, get_config('domain_name')),
            'create_time' => date_time()
        ];
        $jobQueue = config('queue.default');
        \think\Queue::push($jobHandlerClass, $jobData, $jobQueue);

        return $this->fetch('article/viewArticle');
    }

    //删除文章,支持批量删除
    public function deleteArticle($id)
    {
        $ids = explode(',', $id);
        $ArticleModel = new ArticleModel();
        $numRows = $ArticleModel->where([['id', 'in', $ids]])->setField('status', ArticleModel::STATUS_DELETED);

        if ($numRows == count($ids)) {
            $this->success('成功删除!');
        } else {
            $fails = count($ids) - $numRows;
            $this->error("成功删除 $numRows 条，失败 $fails 条!");
        }
    }

    //设定定时发布
    public function setTimingPost()
    {
        if (request()->isGet()) {
            $id = input('id/s', '');
            $this->assign('id', $id);

            return $this->fetch('setTimingPost');
        }

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

    //批量修改分类
    public function batchCategory($ids = null, $cids = null)
    {
        if (request()->isAjax()) {
            if (empty($ids) || empty($cids)) {
                $this->error('请选择文章或分类');
            }

            foreach ($ids as $id) {
                $article = ArticleModel::find($id);
                $article->categorys()->detach();
                $res = $article->categorys()->saveAll($cids);

                if ($res == false) {
                    $this->error('操作失败');
                }
            }

            $this->success('操作成功');
        }

        //文章分类列表
        $CategoryModel = new CategoryModel();
        $categorys = $CategoryModel->getTreeData('tree','sort,id', 'title_cn');
        $this->assign('categorys', $categorys);

        return $this->fetch('batchCategory');
    }

//    //上头条
//    public function upTop()
//    {
//        $data = input('post.');
//        $rule = [
//            'image_id|头条图片' => 'require|number',
//            'title|标题' => 'require',
//        ];
//        $check = $this->validate($data,$rule);
//        if ($check !== true) {
//            $this->error($check);
//        }
//
//        $data['type'] = AdSlotModel::TYPE_BANNER_HEADLINE;
//        $data['create_time'] = date_time();
//        $AdModel = new AdModel();
//        $res = $AdModel->allowField(true)->save($data);
//        if ($res) {
//            $ArticleModel = new ArticleModel();
//            $ArticleModel->where('id', $data['artId'])->setField('ad_id', $AdModel->id);
//            $this->success('成功新增头条');
//        } else {
//            $this->error('新增失败');
//        }
//    }
//
//    //取消头条
//    public function deleteTop()
//    {
//        $adId = input('adId/d', 0);
//        $artId = input('artId/d', 0);
//        if (empty($adId) || empty($artId)) {
//            $this->error('参数错误');
//        }
//        $res = AdModel::destroy($adId);
//        $ArticleModel = new ArticleModel();
//        $res = $ArticleModel->where('id', $artId)->setField('ad_id', 0);
//        if ($res) {
//            $this->success('操作成功');
//        } else {
//            $this->error('操作失败');
//        }
//    }

    //置顶文章
    public function setTop()
    {
        $aid = input('param.id/d');
        $article = ArticleModel::get(['id' => $aid]);
        if (empty($article)) {
            $this->error('文章不存在!');
        }

        $article->is_top = 1; //只用于置顶标记
        $article->sort = ArticleModel::max('sort') + 1;  //实际用于置顶排序
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
        $aid = input('param.id/d');
        $article = ArticleModel::get(['id' => $aid]);
        if (empty($article)) {
            $this->error('文章不存在!');
        }

        $article->is_top = 0;
        $article->sort = 0;
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

        $map = [];
        $key = input('param.key');
        if (!empty($key)) {
            $map[] = ['content', 'like',"%{$key}%"];
        }

        $startTime = input('param.startTime', '');
        $endTime = input('param.endTime', '');

        if (!empty($endTime)) {
            $map[] = ['create_time', '<=', $endTime . ' 23:59:59'];
        }
        if (!empty($startTime)) {
            $map[] = ['create_time', '>=', $startTime . ' 00:00:00'];
        }

        $fields = 'id, content, article_id, create_time, status, author, ip, pid';
        $list = $CommentModel->where($map)->field($fields)->order('create_time desc')->distinct('id')->paginate(20, false);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        $MessageModel = new MessageModel();
        $data['status'] = MessageModel::STATUS_READ;
        $data['read_time'] = date_time();
        $data['is_readed'] = 1;//0未读，1已读
        $MessageModel->save($data, ['type' => MessageModel::TYPE_COMMENT]);

        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);


        return $this->fetch('commentList');
    }

    //审核评论
    public function auditComment($id=0, $pass=1)
    {
        $com = CommentModel::get(['id'=>$id]);
        if (empty($com)) {
            $this->error('评论不存在');
        }

        if ($com->status != CommentModel::STATUS_PUBLISHING) {
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
            $data['status'] = CommentModel::STATUS_PUBLISHED;
            $data['ip'] = request()->ip(0, true);
            $data['article_id'] = $aid;
            $data['content'] = remove_xss($content);
            $data['pid'] = $pid;
            $CommentModel = new CommentModel();
            $result = $CommentModel->save($data);
            if (!$result) {
                $this->error('回复失败');
            } elseif (stripos($_SERVER["HTTP_REFERER"], 'viewComments')) {
                $this->success('回复成功', url("Article/viewComments",['id' => $pid]));
            } else {
                $this->success('回复成功', url('Article/commentList'));
            }
        }

         return $this->fetch('commentList');
    }

    //删除评论
    public function deleteComment($id)
    {
        $ids = explode(',', $id);
        $CommentModel = new CommentModel();

        $numRows = $CommentModel->where([['id', 'in', $ids]])->delete();
        if ($numRows  == count($ids)) {
            $this->success('成功删除');
        } else {
            $fails = count($ids) - $numRows;
            $this->error("成功删除 $numRows 条，失败 $fails 条!");
        }
    }

    //查看评论下的回复
    public  function viewComments($id)
    {
        $comment = CommentModel::get(['id'=>$id]);

        if (empty($comment)) {
            $this->error('评论不存在');
        }

        $CommentModel = new CommentModel();
        $where = [
            'pid' => $comment['id'],
            'status' => CommentModel::STATUS_PUBLISHED
        ];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
        ];
        $comments = $CommentModel->where($where)->order('id desc')->paginate(6, false, $pageConfig);

        $this->assign('comments', $comments);
        $this->assign('comment', $comment);

        return $this->fetch('article/viewComments');
    }

    //文章分类
    public function categoryList()
    {
        if (request()->isPost()) {
            $id = input('post.id', 0);
            $checked = input('post.checked', 'false');
            $category = CategoryModel::get($id);
            if (!$category) {
                $this->error('分类不存在!');
            }

            $msg = "";
            if ($checked == 'true') {
                $category->status = CategoryModel::STATUS_ONLINE;
                $msg = '分类上线成功!';
            } else {
                $category->status = CategoryModel::STATUS_OFFLINE;
                $msg = '分类下线成功!';
            }

            $category->save();

            $this->error($msg);
        }

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
                $this->success('操作成功', url('Article/categoryList'));
            } else {
                $this->error('操作失败');
            }
        }

        //获取默认排序
        $where = [];
        if (input('pid', 0)) {
            $where['pid'] = input('pid', 0);
        }
        $defaultSort = CategoryModel::where($where)->max('sort') + 1;
        $this->assign('defaultSort', $defaultSort);

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
        $slotId = input('param.slot_id', '');

        $where = [];
        if (!empty($title)) {
            $where[] = ['title', 'like', "%$title%"];
        }
        if (!empty($slotId)) {
            $AdServingModel = new AdServingModel();
            $adIds = $AdServingModel->where('slot_id', $slotId)->field('distinct ad_id')->column('ad_id');//column变成一维数组
            $where[] = ['id', 'in', $adIds];
        }

        $AdModel = new AdModel();
        $list = $AdModel->where($where)->order('sort asc,id desc')->paginate(10, false, ['query'=>input('param.')]);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        //广告槽列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id, title_cn')->select();
        $this->assign('slotList', $slotList);

        return $this->fetch('adList');
    }

    //新增广告内链
    public function addAd()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $rule = [
                'title|标题' => 'require',
                'url' => ['require'],
                'slot_ids' => ['require'],
                //'image_id|专题图片' => 'require|number',
            ];
            $check = $this->validate($data, $rule);
            if ($check !== true) {
                $this->error($check);
            }

            $AdModel = new AdModel();
            $data['create_time'] = date_time();
            $rowsNum = $AdModel->isUpdate(false)->allowField(true)->save($data);

            //新增中间表数据
            $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
            $AdModel->adSlots()->attach($data['slot_ids'], $pivot);

            if ($rowsNum !== false) {
                $this->success('成功新增广告', url('article/adList'));
            } else {
                $this->error('新增失败');
            }
        }

        //类型列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id,title_cn,title_en,remark')->select();
        $this->assign('slotList', $slotList);

        return $this->fetch('article/addAd');
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
                'slot_ids' => ['require'],
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

            //更新中间表数据
            $AdModel->adSlots()->detach();
            $pivot = ['update_time' => date_time(), 'create_time' => date_time()];
            $AdModel->adSlots()->attach($data['slot_ids'], $pivot);

            if ($rowsNum !== false) {
                $this->success('成功修改广告', url('article/adList'));
            } else {
                $this->error('修改失败');
            }
        }

        $ad = AdModel::get(['id' => $adId]);
        if (empty($ad)) {
            $this->error('广告不存在');
        }
        $this->assign('ad', $ad);

        //old slots
        $relationSlots = $ad->adSlots;
        $oldSlots = [];
        foreach ($relationSlots as $adSlot) {
            $oldSlots[] = $adSlot['id'];
        }
        $this->assign('oldSlots', $oldSlots);

        //类型列表
        $AdSlotModel = new AdSlotModel();
        $slotList = $AdSlotModel->order('id asc')->field('id,title_cn,title_en')->select();
        $this->assign('slotList', $slotList);

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
