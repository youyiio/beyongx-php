<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2017-07-27
 * Time: 18:20
 */

namespace app\common\logic;

use think\Model;

class MessageLogic extends Model
{

    public function createMessage($receiveUserId, $type, $title, $content, $extra = null)
    {
        if (empty($title) || empty($content) || empty($receiveUserId)) {
            $this->error = "参数为空";
            return false;
        }

        $data = [
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'status' => \app\common\model\MessageModel::STATUS_SEND,
            'receive_user_id' => $receiveUserId,
            'extra' => $extra,
            'send_time' => date_time(),
            'is_readed' => false
        ];

        $message = \app\common\model\MessageModel::create($data);
        if (!$message) {
            return false;
        }

        return $message;
    }

//    public function createMessageToMch($merchantId, $type, $title, $content, $extra = null)
//    {
//        $resultSet = model('UserMerchantRelation')->where(['merchant_id' => $merchantId])->select();
//        if (count($resultSet) == 0) {
//            $this->error = '商户未有关联的用户';
//            return false;
//        }
//
//        $messages = [];
//        foreach ($resultSet as $vo) {
//            $message = $this->createMessage($vo['user_id'], $type, $title, $content, $extra);
//            array_push($messages, $message);
//        }
//
//        return $messages;
//    }


}