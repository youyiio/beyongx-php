<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 17:49
 */

namespace app\admin\controller;


use app\common\model\FeedbackModel as FeedbackModel;
use app\common\model\UserModel;
use think\facade\Log;

class Feedback extends Base
{

    public function index()
    {
        $map[] = ['status', '<=', FeedbackModel::STATUS_REPLY];
        //$map[] = ['reply_client_id', 'exp', 'null'];
//        $map[] = ['reply_feedback_id','exp','is null'];
        $FeedbackModel = new FeedbackModel();
        $list = $FeedbackModel->where($map)->whereNull('reply_client_id')->whereNull('reply_feedback_id')->order('status')->order('create_time desc')->select();
//        dump($GLOBALS);
        $senderArr = [];
        foreach ($list as $k => $value) {
            $sendClientId = $value['send_client_id'];
            $UserModel = new UserModel();
            $user = $UserModel->where('user_id', '=', $sendClientId)->find();

            if ($user) {
                $value['sender'] = $user->nickname;
            } else {//非注册用户
                $value['sender'] = $value['send_client_id'];
            }
            //同一用户信息处理(未读消息统计)
            if (!in_array($value['sender'], $senderArr)) {
                $senderArr[] = $value['sender'];

                if ($value['status'] == FeedbackModel::STATUS_SEND) {
                    $senderArr[$value['sender']]['count']  = 1;
                } else {
                    $senderArr[$value['sender']]['count'] = 0;
                }
            } else {

                if ($value['status'] == FeedbackModel::STATUS_SEND) {
                    $senderArr[$value['sender']]['count'] += 1;
                }
                unset($list[$k]);
            }
        }
        //添加count元素
        foreach ($list as $value) {
            $value['count'] = $senderArr[$value['sender']]['count'];
        }
        //获取状态值
        $sendStatus = FeedbackModel::STATUS_SEND;
        $this->assign('sendStatus',$sendStatus);
        $this->assign('list', $list);

        return view();
    }
    //会话记录显示
    public function chat()
    {
        $feedbackId = input('param.feedback_id');
        $sendClientId = input('param.send_client_id');

        $FeedbackModel = new FeedbackModel();
        $msgList = $FeedbackModel->where('send_client_id', '=', $sendClientId)->whereor('reply_client_id', '=', $sendClientId)->whereor('reply_feedback_id', '=', $feedbackId)->select();

        $UserModel = new UserModel();
        foreach ($msgList as $k => $value) {

            $readTime = date('Y-m-d H:i:s');
            $sendClientIds = $value['send_client_id'];

            if ($value['reply_client_id'] == null && $value['reply_feedback_id'] == null) {

                $user = $UserModel->where('user_id', '=', $sendClientIds)->find();
                if ($user) {//注册用户
                    $value['sender'] = $user->nickname;
                } else { //非注册用户
                    $value['sender'] = $value['send_client_id'];
                }
                //消息状态 send => read
                if ($value['status'] <= FeedbackModel::STATUS_SEND) {
                    $data = ['status' => FeedbackModel::STATUS_READ,'read_time' => $readTime];
                    $where = ['feedback_id' => $value['feedback_id']];
                    FeedbackModel::update($data, $where);
                }
            } else {//管理员
                $admin = $UserModel->where('user_id','=', $sendClientIds)->find();
                $value['sender'] = $admin->nickname;
            }
        }

        $this->assign('feedback_id', $feedbackId);
        $this->assign('send_client_id', $sendClientId);
        $this->assign('msgList', $msgList);

        return view();
    }

    //消息回复
    public function reply()
    {
        $data = input('param.');

        $sendClientId = session('uid');//session();
        $replyFeedbackId = $data['reply_feedback_id'];
        $replyClientId = $data['reply_client_id'];
        $content = $data['content'];
        $status = FeedbackModel::STATUS_SEND;
        $currentTime = date('Y-m-d H:i:s');

        $map = [
            'content' => $content,
            'status' => $status,
            'send_client_id' => $sendClientId,
            'reply_client_id' => $replyClientId,
            'reply_feedback_id' => $replyFeedbackId,
            'create_time' => $currentTime
        ];
        $FeedbackModel = new FeedbackModel();
        $result = $FeedbackModel->save($map);

        if ($result) {
            //消息状态 已读 => 已回复
            $replyTime = date('Y-m-d H:i:s');
            FeedbackModel::update(['status' => FeedbackModel::STATUS_REPLY, 'reply_time' => $replyTime], ['feedback_id' => $replyFeedbackId]);
            $this->success('回复成功','',['replyFeedbackId'=>$replyFeedbackId]);
        } else {
            $this->error('回复失败');
        }
    }



}