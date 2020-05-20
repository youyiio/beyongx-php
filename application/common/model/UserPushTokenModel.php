<?php
namespace app\common\model;

use app\common\library\Os;

class UserPushTokenModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'user_push_token';
    protected $pk = array('uid', 'access_id', 'device_id');

    const STATUS_LOGIN = 1;
    const STATUS_LOGOUT = 2;

    //自动完成
    protected $auto = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    public function createUserPushToken($userId, $accessId, $deviceId, $os, $pushToken)
    {
        $userPushToken = $this->findByUserId($userId, $accessId, $deviceId);
        if ($userPushToken) {
            $data['uid'] = $userId;
            $data['access_id'] = $accessId;
            $data['device_id'] = $deviceId;
            $data['status'] = UserPushTokenModel::STATUS_LOGIN;
            $data['push_token'] = $pushToken;

            $this->isUpdate(true)->save($data);
        } else {
            $data['uid'] = $userId;
            $data['access_id'] = $accessId;
            $data['device_id'] = $deviceId;
            $data['status'] = UserPushTokenModel::STATUS_LOGIN;
            $data['os'] = $os;
            $data['push_token'] = $pushToken;

            $result = $this->save($data);
            if (!$result) {
                return false;
            }
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $userId, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = UserPushTokenModel::get($pk);

        return $userPushToken;
    }

    public function findByUserId($userId, $accessId, $deviceId)
    {
        $where['uid'] = $userId;
        $where['access_id'] = $accessId;
        $where['device_id'] = $deviceId;

        $resultSet = $this->where($where)->limit(1)->select();
        if (count($resultSet) < 1) {
            return false;
        }

        return $resultSet[0];
    }

    public function logout($userId, $accessId, $deviceId)
    {
        $data['uid'] = $userId;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['status'] = UserPushTokenModel::STATUS_LOGOUT;

        $count = $this->isUpdate(true)->save($data);
        if ($count < 1) {
            return false;
        }

        return true;
    }

    public function getAndroidPushTokens($userId, $accessId = '')
    {
        if (empty($accessId)) {
            $accessId = config('middleware_access_id');
        }
        $where = [
            'uid' => $userId,
            'access_id' => $accessId,
            'status' => UserPushTokenModel::STATUS_LOGIN,
            'os' => Os::Android
        ];

        $UserPushTokenModel = new UserPushTokenModel();
        $resultSet = $UserPushTokenModel->where($where)->order('update_time desc')->select();
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
            'status' => UserPushTokenModel::STATUS_LOGIN,
            'os' => Os::iOS
        ];

        $UserPushTokenModel = new UserPushTokenModel();
        $resultSet = $UserPushTokenModel->where($where)->order('update_time desc')->select();
        if (count($resultSet) == 0) {
            return false;
        }

        return $resultSet;
    }
}

?>