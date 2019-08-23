<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-05-29
 * Time: 15:02
 */

namespace app\common\logic;


use app\common\model\ActionLogModel;
use think\Model;

class ActionLogLogic extends Model
{
    //增加日志，data为传递的参数
    public function addLog($userId, $action, $remark, $data=[])
    {
        if (isset($data['password'])) {
            unset($data['password']);
        }

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'module' => request()->module(),
            'ip' => request()->ip(0, true),
            'remark' => $remark,
            'data' => substr(json_encode($data), 0, 128)
        ];

        $ActionLogModel = new ActionLogModel();
        $result = $ActionLogModel->isUpdate(false)->save($data);

        return $result;
    }
}