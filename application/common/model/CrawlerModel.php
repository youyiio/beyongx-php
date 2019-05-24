<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 11:17
 */
namespace app\common\model;

use think\Model;

class CrawlerModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'crawler';

    const STATUS_DELETED = -1; //删除
    const STATUS_DRAFT   = 0;  //草稿
    const STATUS_WAITING  = 1;  //待采集
    const STATUS_CRAWLING  = 2;  //采集中
    const STATUS_CRAWL_FAIL  = 3;  //采集失败
    const STATUS_CRAWL_SUCCESS  = 4;  //采集完成

    protected $auto = ['last_update_time'];
    protected $insert = ['status' => CrawlerModel::STATUS_WAITING, 'create_time', 'last_update_time'];
    protected $update = ['last_update_time'];

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
            1 => '<span class="label label-success">待采集</span>',
            2 => '<span class="label label-success">采集中</span>',
            3 => '<span class="label label-success">采集失败</span>',
            4 => '<span class="label label-success">采集完成</span>',
        ];

        return $status[$data['status']];
    }
}