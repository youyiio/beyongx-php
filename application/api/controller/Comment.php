<?php

namespace app\api\controller;

use app\admin\controller\Message;
use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\model\cms\ArticleModel;
use app\common\model\cms\CommentModel;
use app\common\model\MessageModel;
use app\common\model\UserModel;

class Comment extends Base
{

    protected $payloadData;
    protected $uid;

    public function initialize()
    {
        $this->payloadData = session('jwt_payload_data');
        if (!$this->payloadData) {
            return ajax_error(ResultCode::ACTION_FAILED, 'TOKEN自定义参数不存在！');
        }
        $this->uid = $this->payloadData->uid;
        if (!$this->uid) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }
    }

    //查询评论列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page']?: 1;
        $size = $params['size']?: 10;
        $filters = $params['filters']?: '';

        if (!empty($filters['startTime'])) {
            $where[] = ['create_time', '>=', $filters['startTime'] . '00:00:00'];
        }
        if (!empty($filters['endTime'])) {
            $where[] = ['create_time', '<=', $filters['endTime'] . '23:59:59'];
        }
        if (!empty($filters['keyword'])) {
            $where[] = ['content', 'like', '%'.$filters['keyword'].'%'];
        }

        $where[] = ['status', '=', CommentModel::STATUS_PUBLISHED];

        $CommentModel = new CommentModel();
        $list = $CommentModel->where($where)->paginate($size, false, ['page'=>$page]);
    
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', to_standard_pagelist($list));
    }

    //查询评论内容
    public function query($id)
    {
        $comment = CommentModel::get($id);
        if (empty($comment)) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '评论不存在!');
        }

        $comment = $comment->toArray();
        $returnData = parse_fields($comment, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);   
    }

    //新增评论
    public function create()
    {
        if (get_config('article_comment_switch') === 'false'){
            ajax_return(ResultCode::ACTION_FAILED, '评论失败:评论功能已关闭!');
        }

        $params = $this->request->put();
        //参数言则会那个
        $check = validate('Comment')->scene('create')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('Comment')->getError());
        }

        //查找要评论的文章
        $ArticleModel = new ArticleModel();
        $article = $ArticleModel->field('id,title')->find($params['articleId']);
        if (!$article) {
            ajax_return(ResultCode::E_DATA_NOT_FOUND, '文章不存在!');
        }

        //插入数据
        $data = [
            'article_id' => $params['articleId'],
            'pid' => $params['id']?: '',
            'content' => remove_xss($params['content']),
            'status' => CommentModel::STATUS_PUBLISHED,
            'ip' => request()->ip(0, true),
            'create_time' => date_time()
        ];

        $uid = $this->uid;
        if (empty($uid)) {
            $author = session('visitor');
            $data['author'] = $author;
        } else {
            $user = UserModel::get($uid);
            $author = $user->nickname;
            $data['uid'] = $uid;
            $data['author'] = $author;
        }

        $CommentModel = new CommentModel();
        $comId = $CommentModel->allowField(true)->isUpdate(false)->insertGetId($data);

        if (!$comId) {
            return ajax_return(ResultCode::E_DB_ERROR, '新增失败');
        } 

        //增加评论数量;
        $ArticleModel->where('id', $params['articleId'])->setInc('comment_count');

        //发送评论消息;
        $msgTitle = '新评论消息';
        $msgContent = $author . '评论了文章 “' . $article['title'] . '”';
        send_message(0, 1, $msgTitle, $msgContent, MessageModel::TYPE_COMMENT);

        $data = CommentModel::get($comId);
        $data = $data->toArray();
        $returnData = parse_fields($data, 1);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
        
    }

    //审核评论
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
    
        $CommentModel = new CommentModel();
        $success = $CommentModel->where('id', 'in', $ids)->setField('status', CommentModel::STATUS_PUBLISHED);

        $fails = count($ids) - $success;
        $returnData = ['success'=> $success, 'fail' => $fails];

        return ajax_return(ResultCode::ACTION_SUCCESS, '批量审核成功!', $returnData);

    }

    //删除评论
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
    
        $CommentModel = new CommentModel();
        $success = $CommentModel->where('id', 'in', $ids)->setField('status', CommentModel::STATUS_DELETED);
        
        $fails = count($ids) - $success;
        $returnData = ['success'=> $success, 'fail' => $fails];

        return ajax_return(ResultCode::ACTION_SUCCESS, '删除文章成功!', $returnData);
    }
}