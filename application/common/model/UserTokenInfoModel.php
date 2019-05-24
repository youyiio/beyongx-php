<?php
namespace app\common\model;

use think\Model;
use think\helper\Time;
use youyi\util\StringUtil;

class UserTokenInfoModel extends Model
{
    protected $name = CMS_PREFIX . 'user_token_info';
    protected $pk = array('user_id', 'access_id', 'device_id');

    const STATUS_USABLE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_EXPIRED = 3;


    public function createUserTokenInfo($userId, $accessId, $deviceId) {
        $data['user_id'] = $userId;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = StringUtil::getRandString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime); //30天后过期
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_update_time'] = $data['create_time'];

        //成功返回1
        $result = $this->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['user_id' => $userId, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = UserTokenInfoModel::get($pk);

        return $userPushToken;
    }

    public function updateUserTokenInfo($userId, $accessId, $deviceId) {
        $data['user_id'] = $userId;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = StringUtil::getRandString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime); //30天后过期
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_update_time'] = $data['create_time'];

        //成功返回1
        $result = $this->isUpdate(true)->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['user_id' => $userId, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = UserTokenInfoModel::get($pk);

        return $userPushToken;
    }

    public function findByUserId($userId, $accessId, $deviceId) {
        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['user_id' => $userId, 'access_id' => $accessId, 'device_id' => $deviceId];
        return $this->find($pk);
    }

    public function resetUserTokenInfo($userId, $accessId, $deviceId) {
        $userPushToken = $this->findByUserId($userId, $accessId, $deviceId);
        if (!$userPushToken) {
            return false;
        }

        $data['user_id'] = $userId;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = String::randString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime);; //30天后过期
        $data['last_update_time'] = date('Y-m-d H:i:s');

        //成功返回1
        $result = $this->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['user_id' => $userId, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = UserTokenInfoModel::get($pk);

        return $userPushToken;
    }

}