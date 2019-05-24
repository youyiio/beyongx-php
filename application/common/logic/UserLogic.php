<?php

/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2016-10-07
 * Time: 23:17
 */

namespace app\common\logic;

use app\common\exception\ModelException;
use app\common\library\ResultCode;
use app\common\model\UserPushTokenModel;
use think\exception\DbException;
use think\Model;
use app\common\model\UserTokenInfoModel;
use app\common\model\UserModel;

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

        $userId = $user['user_id'];
        $user->markLogin($userId, $ip);

        return $user;
    }

    public function logout($userId, $accessId, $deviceId)
    {
        $UserPushTokenModel = new UserPushTokenModel();
        return $UserPushTokenModel->logout($userId, $accessId, $deviceId);

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
            throw new ModelException(ResultCode::E_DATA_VERIFY_ERROR, '原始密码不正确!');
        }

        $newPassword = encrypt_password($newPassword, get_config('password_key'));

        $data['user_id'] = $userId;
        $data['password'] = $newPassword;
        $result = $UserModel->isUpdate(true)->save($data);
        if ($result == false) {
            return false;
        }

        $user = $UserModel->find($userId);

        return $user;
    }

    public function savePushToken($userId, $accessId, $deviceId, $os, $androidPushToken, $iosPushToken)
    {
        if (!$os) {
            $os = Os::Android;
        }
        if ($os == Os::Android) {
            $pushToken = $androidPushToken;
        } else if ($os == Os::iOS) {
            $pushToken = $iosPushToken;
        }

        $UserPushTokenModel = new UserPushTokenModel();
        $userPushToken = $UserPushTokenModel->createUserPushToken($userId, $accessId, $deviceId, $os, $pushToken);

        return $userPushToken;
    }

    public function findOrCreateToken($userId, $accessId, $deviceId)
    {
        $UserTokenInfoModel = new UserTokenInfoModel();
        $userTokenInfo = $UserTokenInfoModel->findByUserId($userId, $accessId, $deviceId);
        if (!$userTokenInfo) {
            $userTokenInfo = $UserTokenInfoModel->createUserTokenInfo($userId, $accessId, $deviceId);
        }

        if ($userTokenInfo['status'] == UserTokenInfoModel::STATUS_DISABLED || $userTokenInfo['status'] == UserTokenInfoModel::STATUS_EXPIRED) {
            $userTokenInfo = $UserTokenInfoModel->updateUserTokenInfo($userId, $accessId, $deviceId);
        }
        if (strtotime($userTokenInfo['expire_time']) < time()) {
            $where['user_id'] = $userId;
            $where['access_id'] = $accessId;
            $where['device_id'] = $deviceId;
            $UserTokenInfoModel->where($where)->setField('status', UserTokenInfoModel::STATUS_EXPIRED);

            $userTokenInfo = $UserTokenInfoModel->updateUserTokenInfo($userId, $accessId, $deviceId);
        }

        return $userTokenInfo;
    }

    public function fillUserStuff(&$user, $accessId, $deviceId)
    {
        $userId = $user['user_id'];

        $UserPushTokenModel = new UserPushTokenModel();
        $userPushToken = $UserPushTokenModel->findByUserId($userId, $accessId, $deviceId);
        if ($userPushToken) {
            if ($userPushToken['os'] == Os::Android) {
                $user['android_push_token'] = $userPushToken['push_token'];
            } else if ($userPushToken['os'] == Os::iOS) {
                $user['ios_push_token'] = $userPushToken['push_token'];
            }
        }
        $user['device_id'] = $deviceId;

        $userTokenInfo = $this->findOrCreateToken($userId, $accessId, $deviceId);
        if ($userTokenInfo) {
            $user['token'] = $userTokenInfo['token'];
            $user['expire_time'] = $userTokenInfo['expire_time'];
        }

        return $user;
    }

    /**
     * 开通用户服务
     * @param $inviteUserId
     * @param $inviteCode
     * @param $platform
     * @return $this|bool
     */
//    public function openPlatformService($inviteUserId, $inviteCode, $platform = UserPlatform::PLATFORM_SHOUQIANLA)
//    {
//
//        //1.更新邀请码
//        $userInviteCode = model('UserInviteCode')->getByCode($inviteCode);
//        if (!$userInviteCode) {
//            $this->error = '邀请码不存在!';
//            return false;
//        }
//
//        $data = [
//            'status' => UserInviteCode::STATUS_USED,
//            'invite_user_id' => $inviteUserId,
//        ];
//        $result = $userInviteCode->isUpdate(true)->save($data);
//        if ($result == false) {
//            $this->error = '邀请码更新错误!';
//            return false;
//        }
//
//        //创建用户树关系
//        $level = 1;
//        $parentUser = model('UserRelation')->find(['user_id' => $userInviteCode->user_id]);
//        if ($parentUser) {
//            $level = $parentUser->level;
//        }
//
//        $relationData = [
//            'user_id' => $inviteUserId,
//            'parent_user_id' => $userInviteCode->user_id,
//            'level' => $level,
//        ];
//
//        $relation = UserRelation::create($relationData);
//
//        //3.开通平台服务
//        $platformData = [
//            'user_id' => $inviteUserId,
//            'platform' => $platform,
//            'status' => UserPlatform::STATUS_ACTIVED,
//            'is_vip' => false,
//        ];
//        $userPlatform = UserPlatform::create($platformData);
//        if (!$userPlatform) {
//            $this->error = '平台开通出错!';
//            return false;
//        }
//
//        return $relation;
//    }
}
