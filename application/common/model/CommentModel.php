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

    protected $insert =['status' => 3];

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