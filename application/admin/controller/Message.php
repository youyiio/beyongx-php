<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/25
 * Time: 10:56
 */

namespace app\admin\controller;


use app\common\model\MessageModel;

class Message extends Base
{
    public function index()
    {
        //消息数量
        if (request()->isAjax()) {
            $data = [];

            $MessageModel = new MessageModel();

            //系统消息
            $systemMsgWhere = ['type' => $MessageModel::TYPE_SYSTEM, 'status' => $MessageModel::STATUS_SEND];
            $systemMsgCount = $MessageModel->where($systemMsgWhere)->count();
            $systemMsg = [
                'count' => $systemMsgCount
            ];
            if ($systemMsgCount > 0) {
                $result = $MessageModel->where($systemMsgWhere)->field('id,send_time,create_time')->order("id desc")->find();
                $systemMsg = array_merge($systemMsg, ['time' => friendly_date(strtotime($result['create_time']))]);
            }

            //评论消息
            $commentMsgWhere = ['type' => $MessageModel::TYPE_COMMENT, 'status' => $MessageModel::STATUS_SEND];
            $commentMsgCount = $MessageModel->where($commentMsgWhere)->count();
            $commentMsg = [
                'count' => $commentMsgCount
            ];
            if ($commentMsgCount > 0) {
                $result = $MessageModel->where($commentMsgWhere)->field('id,send_time,create_time')->order("id desc")->find();
                $commentMsg = array_merge($commentMsg, ['time' => friendly_date(strtotime($result['create_time']))]);
            }


            //站内信消息
            $mailMsgWhere = ['type' => $MessageModel::TYPE_MAIL, 'status' => $MessageModel::STATUS_SEND];
            $mailMsgCount = $MessageModel->where($mailMsgWhere)->count();
            $mailMsg = [
                'count' => $mailMsgCount
            ];
            if ($mailMsgCount > 0) {
                $result = $MessageModel->where($mailMsgWhere)->field('id,send_time,create_time')->order("id desc")->find();
                $mailMsg = array_merge($mailMsg, ['time' => friendly_date(strtotime($result['create_time']))]);
            }

            $data['commentMsg'] = $commentMsg;
            $data['systemMsg'] = $systemMsg;
            $data['mailMsg'] = $mailMsg;
            $data['totalMsgCount'] = $commentMsgCount + $systemMsgCount + $mailMsgCount;

            $this->success('ok', '', $data);
        }
    }
}