<?php
/**
 * Created by VSCode.
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
    public function addLog($userId, $action, $remark, $params=[])
    {
        if (isset($params['password'])) {
            unset($params['password']);
        }

        $data = [
            'username' => $userId,
            'action' => $action,
            'module' => request()->module(),
            'ip' => request()->ip(0, true),
            'params' => substr(json_encode($params), 0, 128),
            'user_agent' => request()->header("user-agent"),
            'response' => 'success',
            'remark' => $remark            
        ];

        $ActionLogModel = new ActionLogModel();
        $result = $ActionLogModel->isUpdate(false)->save($data);

        return $result;
    }

    //客户端请求日志
    public function addRequestLog($action, $username, $params=[], $response)
    {
        if (isset($params['password'])) {
            unset($params['password']);
        }

        $data = [
            'username' => $username,
            'action' => $action,
            'module' => request()->module(),
            'component' => request()->url(),
            'ip' => request()->ip(0, true),
            'params' => substr(json_encode($params), 0, 128),
            'user_agent' => request()->header("user-agent"),
            'response' => $response,
            'response_time' => time(),
            'remark' => null,
        ];

        $ActionLogModel = new ActionLogModel();
        $result = $ActionLogModel->isUpdate(false)->save($data);

        return $result;
    }

    //调用其他服务或第三方接口结果日志
    public function addInvokeLog($action, $module, $component, $params=[], $response, $actionTime, $responseTime)
    {
        if (isset($params['password'])) {
            unset($params['password']);
        }

        $data = [
            'username' => null,
            'action' => $action,
            'module' => $module,
            'component' => $component,
            'ip' => null,
            'action_time' => $actionTime,
            'params' => substr(json_encode($params), 0, 128),
            'user_agent' => request()->header("user-agent"),
            'response' => $response,
            'response_time' => $responseTime
        ];

        $ActionLogModel = new ActionLogModel();
        $result = $ActionLogModel->isUpdate(false)->save($data);

        return $result;
    }
}