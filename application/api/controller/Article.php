<?php
namespace app\api\controller;

use app\admin\controller\Image;
use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\logic\ArticleLogic;
use app\common\model\BaseModel;
use app\common\model\cms\ArticleMetaModel;
use app\common\model\cms\ArticleModel;
use app\common\model\cms\CategoryArticleModel;
use app\common\model\cms\CategoryModel;
use app\common\model\cms\CommentModel;
use app\common\model\FileModel;
use app\common\model\ImageModel;
use app\common\model\UserModel;

// 文章相关接口
class Article extends Base
{
    //文章列表
    public function list()
    {
        $ArticleModel = new ArticleModel();

        $params = $this->request->put();
        $check = validate('Article')->scene('list')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('Article')->getError());
        }
     
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;
        $orders = $params['orders'] ?? []; 
        $filters = $params['filters'] ?? []; 

        $where = [];
        if (isset($filters['keyword']) && $filters['keyword']) {
            $where[] = ['keywords|title', 'like', '%'.$filters['keyword'].'%'];
        }
      
        $fields = 'id,title,keywords,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,author';
        if (isset($filters['categoryId']) && $filters['categoryId'] > 0) {
            $childs = CategoryModel::getChild($filters['categoryId']);
            $childCateIds = $childs['ids'];
            array_push($childCateIds, $filters['categoryId']);

            $fields = 'ArticleModel.id,title,keywords,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,author';
            $ArticleModel = ArticleModel::hasWhere('CategoryArticle', [['category_id','in',$childCateIds]], $fields)->group([]); //hack:group用于清理hasmany默认加group key
        }

        //文章状态 
        $status = $filters['status'] ?? '';
        if ($status !== '') {
            $where[] = ['status', '=', $status];
        } else {
            $where[] = ['status', '>=', ArticleModel::STATUS_DRAFT];
        }

        //查询时间
        $queryTimeField = ($status == '' || $status == ArticleModel::STATUS_PUBLISHED) ? 'post_time' : 'create_time';
        if (!empty($filters['startTime'])) {
            $where[] = [$queryTimeField, '>=', $filters['startTime'] . '00:00:00'];
        }
        if (!empty($filters['endTime'])) {
            $where[] = [$queryTimeField, '<=', $filters['endTime'] . '23:59:59'];
        }
        if (empty($orders)) {
            $orders = [
                'sort' => 'desc',
                'post_time' => 'desc',
            ];
        }
        $orders = parse_fields($orders);
     
        $list = $ArticleModel->where($where)->field($fields)->order($orders)->paginate($size, false, ['page'=>$page]);
      
        //添加缩略图和分类
        foreach ($list as $art) {
            $art['thumbImage'] = findThumbImage($art);
            $categorysIds = CategoryArticleModel::where('article_id', '=', $art['id'])->column('category_id');
            $art['categorys'] = CategoryModel::where('id', 'in', $categorysIds)->field('id,name,title')->select();
        }

        $list = $list->toArray();
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '查询成功!', $returnData);
    }

    // 查询文章内容
    public function query($aid) 
    {
        $ArticleModel = new ArticleModel();

        $fields = 'id,title,keywords,description,content,read_count,thumb_image_id,comment_count,author,status,create_time,post_time,update_time';
        $art = $ArticleModel->where('id', $aid)->field($fields)->find();
      
        if (!$art) {
            return ajax_error(ResultCode::E_DATA_NOT_FOUND, '文章不存在');
        }

        //查询文章分类
        $categorysIds = CategoryArticleModel::where('article_id', '=', $art['id'])->column('category_id');
        $categorys = CategoryModel::where('id', 'in', $categorysIds)->field('id,name,title')->select();
       
        //文章标签
        $articleMetaModel = new ArticleMetaModel();
        $tags = $articleMetaModel->_metas($art['id'], 'tag');
        //缩略图
        $thumbImage = findThumbImage($art);
        //附加图片
        $metaImages = findMetaImages($art);
        //附加文件
        $metaFiles = findMetaFiles($art);
      
        //返回数据
        
        $returnData = parse_fields($art->toArray(), 1);
        $returnData['tags'] = $tags;
        $returnData['categorys'] = $categorys;
        $returnData['thumbImage'] = $thumbImage;
        $returnData['metaImages'] = $metaImages;
        $returnData['metaFiles'] = $metaFiles;

        return ajax_return(ResultCode::ACTION_SUCCESS, '查询成功!', $returnData);
    }

    //新增文章
    public function create() 
    {
        //请求的body数据
        $params = $this->request->put();

        //新增文章
        $ArticleModel = new ArticleModel();
        if (get_config('article_audit_switch') === 'false') {
            $status = $ArticleModel::STATUS_PUBLISHED;
        } else {
            $status = $ArticleModel::STATUS_PUBLISHING;        
        }
        $user = $this->user_info;
        $uid = $user->uid;
        
        if (empty($params['author'])) {
            $userModel = new UserModel();
            $author = $userModel->where('id', $uid)->value('nickname');
        } else {
            $author = $params['author'];
        }
        
        $data = [
            'uid' => $uid,
            'title' => $params['title'],
            'description' => $params['description'],
            'keywords' => $params['keywords'],
            'author' => $author,
            'tags' => $params['tags']?? '',
            'content' => remove_xss($params['content']),
            'category_ids' => $params['categoryIds']?? '',
            'thumb_image_id' => $params['thumbImageId']?? '',
            'meta_image_ids' => $params['metaImageIds']?? '',
            'meta_file_ids' => $params['metaFileIds']?? '',
            'status' => $status,
        ];
        $articleLogic = new ArticleLogic();
        $artId = $articleLogic->addArticle($data);
        
        if (!$artId) {
            return ajax_error(ResultCode::E_LOGIC_ERROR, '新增失败');
        }

        //返回数据
     
        $fields = 'id,title,keywords,description,content,read_count,thumb_image_id,comment_count,author,status,create_time,post_time,update_time';
        $art = $ArticleModel->where('id', $artId)->field($fields)->find();

        //标签
        $articleMetaModel = new ArticleMetaModel();
        $tags = $articleMetaModel->_metas($artId, 'tag');
        //缩略图
        $thumbImage = findThumbImage($art);
        //附加图片
        $metaImages = findMetaImages($art);
        //附加文件
        $metaFiles = findMetaFiles($art);
      
        //返回数据
        $returnData = parse_fields($art->toArray(), 1);
        $returnData['tags'] = $tags;
        $returnData['thumbImage'] = $thumbImage;
        $returnData['metaImages'] = $metaImages;
        $returnData['metaFiles'] = $metaFiles;

        return ajax_return(ResultCode::ACTION_SUCCESS, '创建成功!', $returnData);
    }

    public function edit($aid) 
    {
        //请求的body数据
        $params = $this->request->put();
        $params = parse_fields($params, 0);

        if ($aid != $params['id']) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误!');
        }
        
        //更新数据
        $articleLogic = new ArticleLogic();
        $res = $articleLogic->editArticle($params);
        if(!$res){
            return ajax_return(ResultCode::E_DB_ERROR, '更新失败');
        }

        //返回数据
        $articleModel = new ArticleModel();
        $fields = 'id,title,keywords,description,content,read_count,comment_count,author,status,create_time,post_time,update_time,thumb_image_id';
        $art = $articleModel->where('id', '=', $aid)->field($fields)->find();
        
        //标签
        $articleMetaModel = new ArticleMetaModel();
        $tags = $articleMetaModel->_metas($aid, 'tag');
        //缩略图
        $thumbImage = findThumbImage($art);
        //附加图片
        $metaImages = FindMetaImages($art);
        //附加文件
        $metaFiles = findMetaFiles($art);

        //返回json格式
        $returnData = parse_fields($art->toArray(),1);
        $returnData['tags'] = $tags;
        $returnData['thumbImage'] = $thumbImage;
        $returnData['metaFiles'] = $metaFiles;
        $returnData['metaImages'] = $metaImages;
          
        return ajax_return(ResultCode::ACTION_SUCCESS, '更新成功', $returnData);
    }

    //发布文章
    public function publish()
    {
        $params = $this->request->put();
       
        //参数验证
        if (count($params) !== 1) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }
        if (isset($params['id']) && is_numeric($params['id'])) {
            $ids[] = $params['id']; 
        } elseif (isset($params['ids']) && is_array($params['ids'])) {
            $ids = $params['ids'];     
        } else {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }

        $data = [
            'status' => ArticleModel::STATUS_PUBLISHING,
            'post_time' => date_time()
        ];
        //审核开关关闭时
        if (get_config('article_audit_switch') === 'false') {
            $data['status'] = ArticleModel::STATUS_PUBLISHED;
        }

        //发布文章
        $ArticleModel = new ArticleModel();
        $success = $ArticleModel->where('id', 'in', $ids)->setField($data);
        $fails = count($ids) - $success;

        $returnData = ['success'=> $success, 'fail' => $fails];
        return ajax_return(ResultCode::ACTION_SUCCESS, '发布文章成功!', $returnData);
        
    }

    //审核文章
    public function audit()
    {
        $params = $this->request->put();
      
        //参数验证
        if (count($params) !== 1) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }
        if (isset($params['id']) && is_numeric($params['id'])) {
            $ids[] = $params['id']; 
        } elseif (isset($params['ids']) && is_array($params['ids'])) {
            $ids = $params['ids'];     
        } else {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }

        $data = [
            'status' => ArticleModel::STATUS_PUBLISHED,
            'post_time' => date_time()
        ];

        $ArticleModel = new ArticleModel();
        $success = $ArticleModel->where('id', 'in', $ids)->setField($data);
        $fails = count($ids) - $success;

        $returnData = ['success'=> $success, 'fail' => $fails];
        return ajax_return(ResultCode::ACTION_SUCCESS, '审核文章成功!', $returnData);
    }


    //删除文章
    public function delete() 
    {
        $params = $this->request->put();
       
        //参数验证
        if (count($params) !== 1) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }
        if (isset($params['id']) && is_numeric($params['id'])) {
            $ids[] = $params['id']; 
        } elseif (isset($params['ids']) && is_array($params['ids'])) {
            $ids = $params['ids'];     
        } else {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误');
        }

        $ArticleModel = new ArticleModel();
        $success = $ArticleModel->where('id', 'in', $ids)->setField('status', ArticleModel::STATUS_DELETED);
        $fails = count($ids) - $success;

        $returnData = ['success'=> $success, 'fail' => $fails];
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //查询文章评论
    public function comments($id)
    {
        $article = ArticleModel::get($id);
        if (empty($article)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '文章不存在');
        }

        $params = $this->request->put();
        $page = $params['page']?? 1;
        $size = $params['size']?? 5;
        $filters = $params['filters']?? '';
        $keyword = $filters['keyword']?? '';
        //查询评论
        $CommentModel = new CommentModel();
        $where =[
            ['content', 'like', '%'.$keyword.'%'],
            ['article_id', '=', $id],
            ['status', '=', CommentModel::STATUS_PUBLISHED]
        ];
      
        $list = $CommentModel->where($where)->paginate($size, false, ['page'=>$page]);

        return ajax_return(ResultCode::ACTION_SUCCESS, '查询成功!', to_standard_pagelist($list));
    }

}