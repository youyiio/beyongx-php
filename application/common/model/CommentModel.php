<?php
namespace app\common\model;



class CommentModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'comment';

    const STATUS_DELETED    = -1; //删除
    const STATUS_DRAFT      = 0;  //草稿
    const STATUS_PUBLISHING = 1;  //申请发布
    const STATUS_REFUSE     = 2;  //拒绝
    const STATUS_PUBLISHED  = 3;  //已发布

    //属性：status_text
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            -1 => '删除',
            0 => '草稿',
            1 => '申请发布',
            2 => '拒绝',
            3 => '发布',
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }

    //属性：status_html
    public function getStatusHtmlAttr($value, $data)
    {

        $status = [
            -1 => '<span class="label label-danger">删除</span>',
            0 => '<span class="label label-default">草稿</span>',
            1 => '<span class="label label-info">申请发布</span>',
            2 =>  '<span class="label label-warning">拒绝</span>',
            3 => '<span class="label label-primary">已发布</span>',
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }


    //表关联:文章
    public function article()
    {
        return $this->hasOne('ArticleModel', 'id', 'article_id');
    }

    //表自连接:回复
    public function reply()
    {
        return $this->hasOne('CommentModel','id','pid');
    }

    //表自连接:回复
    public function replys()
    {
        return $this->hasMany('CommentModel','id','pid');
    }


}