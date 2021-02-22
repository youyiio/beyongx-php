<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-12-25
 * Time: 14:24
 */

namespace app\common\model;


use think\Model;

class MessageModel extends Model
{
    protected $name = CMS_PREFIX . 'message';

    const STATUS_DELETED = -1; //删除
    const STATUS_DRAFT = 0; //草稿，0为世间最自然的数，出生就是这个状态
    const STATUS_APPLY = 1; //提交
    const STATUS_SEND = 2; //已发送
    const STATUS_READ = 3; //已阅读

    const UID_ALL = 'all'; //表示uid为所有的人
    const UID_SYSTEM = 'sys'; //表示uid为系统

    const TYPE_SYSTEM = 'system'; //系统消息
    const TYPE_MAIL = 'mail';   //站内信
    const TYPE_COMMENT = 'comment'; //评论

    //属性：status_text
    public function getStatusTextAttr($value ,$data)
    {
        $status = [
            -1 => '已删除',
            0 => '草稿',
            1 => '提交',
            2 => '已发送',
            3 => '已阅读',
        ];

        return isset($status[$data['status']]) ? $status[$data['status']] : '未知状态';
    }
}