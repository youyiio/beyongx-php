<?php
namespace app\common\logic;

use think\Model;
use app\common\exception\ModelException;
use app\common\library\ResultCode;
use app\common\model\UserModel;
use app\common\model\api\TokenModel;

class UserLogic extends Model
{

    public function register($mobile, $password, $nickname = '', $email = '', $account = '', $status = UserModel::STATUS_ACTIVED)
    {
        $UserModel = new UserModel();
        $user = $UserModel->createUser($mobile, $password, $nickname, $email, $account, $status);
        if (!$user) {
            $this->error = $UserModel->error;
            return false;
        }

        return $user;
    }

    /**
     * @param $account 邮箱或手机号
     * @param $password 密码
     * @param $ip
     * @return Model
     * @throws ModelException
     */
    public function login($account, $password, $ip='127.0.0.1')
    {
        $UserModel = new UserModel();
        $user = $UserModel->checkUser($account, $password);
        if (!$user) {
            throw new ModelException(ResultCode::E_USER_NOT_EXIST, '帐号不正确');
        }

        switch ($user->status) {
            case UserModel::STATUS_APPLY:
                throw new ModelException(ResultCode::E_USER_STATE_NOT_ACTIVED, '用户未激活');
                break;
            case UserModel::STATUS_FREEZED:
                throw new ModelException(ResultCode::E_USER_STATE_FREED, '用户已冻结');
                break;
            case UserModel::STATUS_DELETED:
                throw new ModelException(ResultCode::E_USER_STATE_DELETED, '用户已删除');
                break;
            default:
                break;
        }

        $userId = $user['id'];
        $user->markLogin($userId, $ip);

        return $user;
    }

    public function logout($userId, $accessId, $deviceId)
    {
        //$PushTokenModel = new PushTokenModel();
        //return $PushTokenModel->logout($userId, $accessId, $deviceId);
    }

    public function modifyPassword($userId, $oldPassword, $newPassword)
    {
        $UserModel = new UserModel();
        $user = $UserModel->find($userId);
        if (!$user) {
            throw new ModelException(ResultCode::E_USER_NOT_EXIST, '用户不存在!');
        }

        $tempPassword = encrypt_password($oldPassword, get_config('password_key'));
        if ($tempPassword != $user->password) {
            throw new ModelException(ResultCode::E_DATA_VALIDATE_ERROR, '原始密码不正确!');
        }

        $newPassword = encrypt_password($newPassword, get_config('password_key'));

        $data['id'] = $userId;
        $data['password'] = $newPassword;
        $result = $UserModel->isUpdate(true)->save($data);
        if ($result == false) {
            return false;
        }

        $user = $UserModel->find($userId);

        return $user;
    }

    public function findOrCreateToken($userId, $accessId, $deviceId)
    {
        $TokenModel = new TokenModel();
        $tokenInfo = $TokenModel->findByUserId($userId, $accessId, $deviceId);
        if (!$tokenInfo) {
            $tokenInfo = $TokenModel->createTokenInfo($userId, $accessId, $deviceId);
        }

        if ($tokenInfo['status'] == TokenModel::STATUS_DISABLED || $tokenInfo['status'] == TokenModel::STATUS_EXPIRED) {
            $userTokenInfo = $TokenModel->updateTokenInfo($userId, $accessId, $deviceId);
        }
        if (strtotime($tokenInfo['expire_time']) < time()) {
            $where['uid'] = $userId;
            $where['access_id'] = $accessId;
            $where['device_id'] = $deviceId;
            $TokenModel->where($where)->setField('status', TokenModel::STATUS_EXPIRED);

            $tokenInfo = $TokenModel->updateTokenInfo($userId, $accessId, $deviceId);
        }

        return $tokenInfo;
    }

    public function fillUserStuff(&$user, $accessId, $deviceId)
    {
        $userId = $user['id'];

        $user['device_id'] = $deviceId;

        $tokenInfo = $this->findOrCreateToken($userId, $accessId, $deviceId);
        if ($tokenInfo) {
            $user['token'] = $tokenInfo['token'];
            $user['expire_time'] = $tokenInfo['expire_time'];
        }

        return $user;
    }

}
