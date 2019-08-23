<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 10:39
 */

namespace app\cms\controller;

use app\common\model\FeedbackModel as FeedbackModel;
use app\common\model\UserModel;
use think\Controller;

/**
 * 用户反馈
 **/
class Feedback extends Base
{
    public function index()
    {
        if (session('uid')) {
            $user = session('uid');
        } elseif (session('visitor')) {
            $user = session('visitor');
        } else {
            $visitor = '游客-'.ip2long(ip());
            session('visitor', $visitor);
            $user = session('visitor') ;
        }

        $FeedbackModel = new FeedbackModel();
        $total = $FeedbackModel->where('send_client_id','=',$user)->whereOr('reply_client_id','=',$user)->count();
        $length = 8;
        if ($total <= $length) {
            $msgList = $FeedbackModel->where('send_client_id','=', $user)->whereOr('reply_client_id','=', $user)->order('create_time')->select();
        } else {
            $offset = $total - $length;
            $msgList = $FeedbackModel->where('send_client_id','=', $user)->whereOr('reply_client_id','=', $user)->limit($offset,$length)->order('create_time')->select();
        }

        if ($user == session('uid')) {
            $UserModel = new UserModel();
            $userInfo = $UserModel->where('user_id','=',$user)->find();
            foreach ( $msgList as $value) {
                $value['send_client_id'] = $userInfo['nickname'];
            }
        }

        $msgList = $FeedbackModel->order('create_time desc')->paginate(6,false);
        $this->assign('total', $total);
        $this->assign('msgList', $msgList);

        return $this->fetch('index');
    }

    public function data()
    {
        if (session('uid')) {
            $user = session('uid');
        } else {
            $user = session('visitor');
//            $user = '吴迪';
        }

        $length = 8 ;
        $total = input('post.total');
        $offset = input('post.offset');// length = 8
        if ($total <= $offset + $length) {
            $length = $total - $offset;
        }
        $FeedbackModel = new FeedbackModel();
        $result = $FeedbackModel->where('send_client_id','=',$user)->whereOr('reply_client_id','=',$user)->limit($offset,$length)->order('create_time desc')->select();

        return array('result' => $result, 'status' => 1, 'msg'=>'获取成功！');

    }

    public function sendMsg()
    {
        if (session('uid')) {
            $user = session('uid');
        } else {
            $user = session('visitor');
        }
        if(!request()->isPost()){
            $this->error('请求错误！');
        }
        $data = input('param.');
        $send_client_id = $user;
        $content = $data['content'];
        $status = FeedbackModel::STATUS_SEND;
        $dateTime = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'];

        $map = [
            'status' => $status,
            'content' => $content,
            'send_client_id' => $send_client_id,
            'ip' => $ip,
            'create_time' => $dateTime
        ];
        $FeedbackModel = new FeedbackModel();
        $result = $FeedbackModel->save($map);

        if ($result) {
            $this->redirect('Feedback/index');
        } else {
            $this->error('发送失败');
        }
    }

}