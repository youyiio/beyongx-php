<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2017-07-06
 * Time: 10:30
 */

namespace app\common\model;


class FeedbackModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'feedback';

    const STATUS_DELETED = -1; //删除
    const STATUS_APPLY = 1; //提交       --针对需要对接第三方平方设计如微信
    const STATUS_SEND = 2; //已发送
    const STATUS_READ = 3;  //已读
    const STATUS_REPLY = 4;  //已回复

    const SOURCE_WEBSITE = 'website';
    const SOURCE_APP = 'app';
    const SOURCE_WECHAT = 'wechat';

    //属性：status_text
    public function getStatusTextAttr($value ,$data)
    {
        $status = [
            -1 => '已删除',
            1 => '提交',
            2 => '已发送',
            3 => '已读',
            4 => '已回复',
        ];

        return isset($status[$data['status']]) ? $status[$data['status']] : '未知状态';
    }

}