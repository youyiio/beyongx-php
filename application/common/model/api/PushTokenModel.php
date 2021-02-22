<?php
namespace app\common\model\api;

use app\common\model\BaseModel;
use app\common\library\Os;

class PushTokenModel extends BaseModel
{
    protected $name = 'api_push_token';
    protected $pk = "id";

    const STATUS_LOGIN = 1;
    const STATUS_LOGOUT = 2;

    //自动完成
    protected $auto = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    public function createPushToken($uid, $accessId, $deviceId, $os, $pushToken)
    {
        $userPushToken = $this->findByUserId($uid, $accessId, $deviceId);
        if ($userPushToken) {
            $data['uid'] = $uid;
            $data['access_id'] = $accessId;
            $data['device_id'] = $deviceId;
            $data['status'] = PushTokenModel::STATUS_LOGIN;
            $data['push_token'] = $pushToken;

            $this->isUpdate(true)->save($data);
        } else {
            $data['uid'] = $uid;
            $data['access_id'] = $accessId;
            $data['device_id'] = $deviceId;
            $data['status'] = PushTokenModel::STATUS_LOGIN;
            $data['os'] = $os;
            $data['push_token'] = $pushToken;

            $result = $this->save($data);
            if (!$result) {
                return false;
            }
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = PushTokenModel::get($pk);

        return $userPushToken;
    }

    public function findByUserId($uid, $accessId, $deviceId)
    {
        $where['uid'] = $uid;
        $where['access_id'] = $accessId;
        $where['device_id'] = $deviceId;

        $resultSet = $this->where($where)->limit(1)->select();
        if (count($resultSet) < 1) {
            return false;
        }

        return $resultSet[0];
    }

    public function logout($uid, $accessId, $deviceId)
    {
        $data['uid'] = $uid;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['status'] = PushTokenModel::STATUS_LOGOUT;

        $count = $this->isUpdate(true)->save($data);
        if ($count < 1) {
            return false;
        }

        return true;
    }

    public function getAndroidPushTokens($uid, $accessId = '')
    {
        if (empty($accessId)) {
            $accessId = config('middleware_access_id');
        }
        $where = [
            'uid' => $uid,
            'access_id' => $accessId,
            'status' => PushTokenModel::STATUS_LOGIN,
            'os' => Os::Android
        ];

        $PushTokenModel = new PushTokenModel();
        $resultSet = $PushTokenModel->where($where)->order('update_time desc')->select();
        if (count($resultSet) == 0) {
            return false;
        }

        return $resultSet;
    }

    public function getIosPushTokens($userId, $accessId = '')
    {
        if (empty($accessId)) {
            $accessId = config('middleware_access_id');
        }
        $where = [
            'uid' => $userId,
            'access_id' => $accessId,
            'status' => PushTokenModel::STATUS_LOGIN,
            'os' => Os::iOS
        ];

        $PushTokenModel = new PushTokenModel();
        $resultSet = $PushTokenModel->where($where)->order('update_time desc')->select();
        if (count($resultSet) == 0) {
            return false;
        }

        return $resultSet;
    }
}
