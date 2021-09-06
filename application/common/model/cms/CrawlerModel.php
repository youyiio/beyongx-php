<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;

class CrawlerModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'crawler';

    const STATUS_DELETED = -1; //删除
    const STATUS_DRAFT   = 0;  //草稿
    const STATUS_WAITING  = 1;  //待采集
    const STATUS_CRAWLING  = 2;  //采集中
    const STATUS_CRAWL_FAIL  = 3;  //采集失败
    const STATUS_CRAWL_SUCCESS  = 4;  //采集完成

    protected $auto = ['update_time'];
    protected $insert = ['status' => CrawlerModel::STATUS_WAITING, 'create_time', 'update_time'];
    protected $update = ['update_time'];

    //属性：status_text
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            -1 => '删除',
            0 => '草稿',
            1 => '待采集',
            2 => '采集中',
            3 => '采集失败',
            4 => '采集完成',
        ];

        return isset($status[$data['status']]) ? $status[$data['status']] : '未知状态';
    }

    //属性：status_html
    public function getStatusHtmlAttr($value, $data)
    {
        $status = [
            -1 => '<span class="label label-danger">删除</span>',
            0 => '<span class="label label-default">草稿</span>',
            1 => '<span class="label label-primary">待采集</span>',
            2 => '<span class="label label-info">采集中</span>',
            3 => '<span class="label label-warning">采集失败</span>',
            4 => '<span class="label label-success">采集完成</span>',
        ];

        return $status[$data['status']];
    }

    //关联表: 文章分类
    protected function category()
    {
        return $this->belongsTo('CategoryModel', 'category_id');
    }
}