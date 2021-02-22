<?php
namespace app\common\model\api;

use app\common\model\BaseModel;
use think\helper\Time;
use youyi\util\StringUtil;

class TokenModel extends BaseModel
{
    protected $name = 'api_token';
    protected $pk = 'id';

    const STATUS_USABLE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_EXPIRED = 3;

    //自动完成
    protected $auto = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    public function createTokenInfo($uid, $accessId, $deviceId)
    {
        $data['uid'] = $uid;
        $data['access_id'] = $accessId;
        $data['device_id'] = $deviceId;
        $data['token'] = StringUtil::getRandString(18);

        $data['status'] = TokenModel::STATUS_USABLE;
        $expireTime = Time::daysAfter(30);
        $data['expire_time'] = date('Y-m-d H:i:s', $expireTime); //30天后过期
        $data['create_time'] = date_time();
        $data['update_time'] = date_time();

        //成功返回1
        $id = $this->insertGetId($data);
        if (!$id) {
            return false;
        }

        $tokenInfo = TokenModel::get($id);
        return $tokenInfo;
    }

    // 更新token
    public function updateTokenInfo($uid, $accessId, $deviceId)
    {
        $where['uid'] = $uid;
        $where['access_id'] = $accessId;
        $where['device_id'] = $deviceId;

        $tokenInfo = TokenModel::find($where);
        if (!$tokenInfo) {
            return false;
        }

        $tokenInfo->token = StringUtil::getRandString(18);
        $tokenInfo->status = TokenModel::STATUS_USABLE;
        $expireTime = Time::daysAfter(30);
        $tokenInfo->expire_time = date('Y-m-d H:i:s', $expireTime); //30天后过期

        //成功返回1
        $result = $tokenInfo->save();
        if (!$result) {
            return false;
        }

        $tokenInfo = TokenModel::get($tokenInfo->id);

        return $tokenInfo;
    }

    public function findByUserId($uid, $accessId, $deviceId)
    {
        //联合主键，find设置方法；顺序与pk字段一致
        $pk = ['uid' => $uid, 'access_id' => $accessId, 'device_id' => $deviceId];
        return $this->find($pk);
    }

    // 重置token
    public function resetTokenInfo($uid, $accessId, $deviceId)
    {
        $tokenInfo = $this->findByUserId($uid, $accessId, $deviceId);
        if (!$tokenInfo) {
            return false;
        }

        return $this->updateTokenInfo($uid, $accessId, $deviceId);
    }

}