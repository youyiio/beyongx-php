<?php
namespace app\api\controller;

// 不需要认证的话继承Base
use app\api\controller\Base;
use app\common\library\ResultCode;
use app\common\model\cms\ArticleModel;

// 需要登录验证的继承JwtBase
// use app\api\controller\JwtBase;

class Post extends Base
{
    //查询列表
    public function list($page=1, $size=10) {
        $where = [
            "status" => ArticleModel::STATUS_PUBLISHED
        ];
        $fields = 'id,title,thumb_image_id,post_time,update_time,create_time,is_top,status,read_count,sort,relateds';
        $order = [
            'sort' => 'desc',
            'post_time' => 'desc',
        ];
        $pageConfig = [
            'query' => ['page' => $page]
        ];

        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where($where)->field($fields)->order($order)->paginate($size, false, $pageConfig);

        return ajax_success(to_standard_pagelist($list));
    }

    // crud 增删查改
    public function query($aid) {
        $article = ArticleModel::get($aid);

        return ajax_success($article);
    }

    public function create() {
        $data = input("post.");
        $article = ArticleModel::create($data);

        return ajax_return(ResultCode::ACTION_SUCCESS, '创建成功', $article);
    }

    public function edit($aid) {
        $data = input("post.");
        $article = ArticleModel::update($data, ["id" => $aid]);

        return ajax_success($article);
    }

    public function delete($aid) {
        $data = [
            "status" => ArticleModel::STATUS_DELETED
        ];
        $res = ArticleModel::update($data, $aid);

        return ajax_return(ResultCode::ACTION_SUCCESS, '删除成功');
    }
}