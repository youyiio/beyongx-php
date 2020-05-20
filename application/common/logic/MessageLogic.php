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
            'to_uid' => $receiveUserId,
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

}