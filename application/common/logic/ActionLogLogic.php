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

    public function addLog($userId, $action, $remark, $data=[])
    {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'module' => request()->module(),
            'ip' => request()->ip(0, true),
            'remark' => $remark,
            'data' => json_encode($data)
        ];

        $ActionLogModel = new ActionLogModel();
        $result = $ActionLogModel->isUpdate(false)->save($data);

        return $result;
    }
}