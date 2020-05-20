<?php
namespace app\common\model;

use think\helper\Time;
use youyi\util\StringUtil;

class UserTokenInfoModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'user_token_info';
    protected $pk = array('uid', 'access_id', 'device_id');

    const STATUS_USABLE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_EXPIRED = 3;

    //自动完成
    protected $auto = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    public function createUserTokenInfo($uid, $accessId, $deviceId)
    {
        $data['uid'] = $uid;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;

        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = StringUtil::getRandString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime); //30天后过期

        //成功返回1
        $result = $this->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userToken = UserTokenInfoModel::get($pk);

        return $userToken;
    }

    public function updateUserTokenInfo($uid, $accessId, $deviceId)
    {
        $data['uid'] = $uid;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;

        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = StringUtil::getRandString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime); //30天后过期

        //成功返回1
        $result = $this->isUpdate(true)->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userPushToken = UserTokenInfoModel::get($pk);

        return $userPushToken;
    }

    public function findByUserId($uid, $accessId, $deviceId)
    {
        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        return $this->find($pk);
    }

    public function resetUserTokenInfo($uid, $accessId, $deviceId)
    {
        $userTokenInfo = $this->findByUserId($uid, $accessId, $deviceId);
        if (!$userTokenInfo) {
            return false;
        }

        $data['uid'] = $uid;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;

        $data['status'] = UserTokenInfoModel::STATUS_USABLE;
        $data['token'] = StringUtil::getRandString(18);
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime);; //30天后过期

        //成功返回1
        $result = $this->save($data);
        if (!$result) {
            return false;
        }

        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        $userTokenInfo = UserTokenInfoModel::get($pk);

        return $userTokenInfo;
    }

}