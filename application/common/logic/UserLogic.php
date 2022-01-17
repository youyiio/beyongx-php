<?php
namespace app\common\logic;

use think\Model;
use app\common\exception\ModelException;
use app\common\library\ResultCode;
use app\common\model\UserModel;
use app\common\model\api\TokenModel;
use beyong\commons\utils\StringUtils;
use think\facade\Cookie;

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

    //新增用户
    public function createUser($mobile, $password, $nickname = '', $email = '', $account = '', $deptId, $status = UserModel::STATUS_ACTIVED)
    {
        $UserModel = new UserModel();
        if (empty($nickname)) {
            $nickname = $mobile;
        }
        if ($UserModel->findByMobile($mobile)) {
            throw new ModelException(ResultCode::E_USER_MOBILE_HAS_EXIST, '手机号已经存在');
        }
        if (empty($email)) {
            $email = $mobile .'@' . StringUtils::getRandString(6) . '.com';
        } else if ($UserModel->findByEmail($email)) {
            throw new ModelException(ResultCode::E_USER_EMAIL_HAS_EXIST, '邮箱已经存在');
        }
        if (empty($account)) {
            $account = StringUtils::getRandString(12);
        } else if ($UserModel->where(['account' => $account])->find()) {
            throw new ModelException(ResultCode::E_USER_ACCOUNT_HAS_EXIST, '帐号已经存在');
        }

        
        $user = new UserModel();
        $user->mobile = $mobile;
        $user->email = $email;
        $user->account = $account;
        $user->password = encrypt_password($password, get_config('password_key'));
        $user->status = $status;
        $user->nickname = $nickname;
        $user->dept_id = $deptId;
        $currentTime = date('Y-m-d H:i:s');
        $user->register_time = $currentTime;
        $user->register_ip = request()->ip(0, true);

        //设置来源及入口url
        if (Cookie::has('from_referee') || Cookie::has('entrance_url')) {
            $user->from_referee = Cookie::get('from_referee');
            $user->entrance_url = Cookie::get('entrance_url');
        }

        $user->save();
        return $user->id;
    }

    public function updateUser($uid, $params)
    {
        $UserModel = new UserModel();

        if (isset($params['mobile']) && !$this->uniqueMobile($uid, $params['mobile'])) {
            throw new ModelException(ResultCode::E_USER_MOBILE_HAS_EXIST, '手机号已经存在');
        } else if (isset($params['email']) && !$this->uniqueEmail($uid, $params['email'])) {
            throw new ModelException(ResultCode::E_USER_MOBILE_HAS_EXIST, '邮箱已经存在');
        } else if (isset($params['account']) && !$this->uniqueAccount($uid, $params['account'])) {
            throw new ModelException(ResultCode::E_USER_ACCOUNT_HAS_EXIST, '帐号已经存在');
        }

        $res = $UserModel->isUpdate(true)->allowField(true)->save($params, ['id' => $uid]);

        return $res;
    }

    //是否唯一，是的话返回true
    public function uniqueMobile($uid, $mobile)
    {
        $where = [
            ['mobile', '=', $mobile],
            ['id', '<>', $uid],
        ];

        $UserModel = new UserModel();
        $users = $UserModel->where($where)->limit(1)->select();
        if (count($users) > 0) {
            return false;
        }

        return true;
    }

    public function uniqueEmail($uid, $email)
    {
        $where = [
            ['email', '=', $email],
            ['id', '<>', $uid],
        ];

        $UserModel = new UserModel();
        $users = $UserModel->where($where)->limit(1)->select();
        if (count($users) > 0) {
            return false;
        }

        return true;
    }

    public function uniqueAccount($uid, $account)
    {
        $where = [
            ['account', '=', $account],
            ['id', '<>', $uid],
        ];

        $UserModel = new UserModel();
        $users = $UserModel->where($where)->limit(1)->select();
        if (count($users) > 0) {
            return false;
        }

        return true;
    }
}
