<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2016/10/8
 * Time: 13:58
 */
namespace app\common\logic;

use think\Exception;
use think\facade\Env;
use think\Model;
use app\common\model\UserModel;
use app\common\model\UserVerifyCodeModel;
use think\facade\Config;

use youyi\util\PregUtil;
use youyi\util\StringUtil;

class CodeLogic extends Model
{

    /**
     * 发送激活邮件，条件：1、设置邮件发送配置；2、发送的邮件模板,theme/xxx/email/email_template.html
     * @param $email
     * @param $activeAction, 格式为：module/controller/action
     * @return bool
     * @throws \mailer\lib\Exception
     * @throws Exception
     */
    public function sendActiveMail($email, $activeAction)
    {
        $UserModel = new UserModel();
        $user = $UserModel->findByEmail($email);
        if (empty($user)) {
            $this->error = '邮箱不存在';
            return false;
        }

        $code = StringUtil::getRandString(12);
        $UserVerifyCodeModel = new UserVerifyCodeModel();
        $verifyCode = $UserVerifyCodeModel->createVerifyCode(UserVerifyCodeModel::TYPE_MAIL_ACTIVE, $email, $code, 24 * 60 * 60);
        if (!$verifyCode) {
            $this->error = '验证码生成失败!';
            return false;
        }

        $config = Config::pull('theme');

        $uid = $user['user_id'];
        $url = url($activeAction, ['code' => $verifyCode['code'], 'email' => $email], false, true);

//        if (isset($config['responsive']) && $config['responsive'] == true) {
//            array_push($tplPaths, 'pc'); //默认放于pc下
//        }
//        array_push($tplPaths, 'email');
//        array_push($tplPaths, 'mail_active.tpl');

        $subject = '新用户激活';
        $message = get_config('email_activate_user');
        $data = [
            'url' => $url,
        ];

        $res = send_mail($email, $subject, $message, true, $data);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送注册短信验证码，条件：1、配置短信通道；
     * @param $mobile
     * @throws Exception
     */
    public function sendRegisterSms($mobile)
    {
        $channel = get_config('sms_channel');
        if (empty($channel)) {
            throw new Exception("未配置短信通道");
        }

        throw new Exception("短信发送正在实现中...");
    }

    /**
     * 发送重置的验证码
     * @param $email
     * @return bool|\mix
     * @throws \mailer\lib\Exception
     */
    public function sendResetMail($email)
    {
        if (!PregUtil::isEmail($email)) {
            $this->error = '邮箱格式不正确';
            return false;
        }

        //验证账号是否存在
        $UserModel = new UserModel();
        $user = $UserModel->findByEmail($email);
        if (empty($user)) {
            $this->error = '邮箱不存在';
            return false;
        }

        $mark = $user['user_id'] . '_send_reset_count';
        if (cache($mark) >= 5 && !config('app_debug')) {
            $this->error = '您今天请求重置码次数已经超限!';
            return false;
        }

        $code = StringUtil::getRandString(12);
        $UserVerifyCodeModel = new UserVerifyCodeModel();
        $verifyCode = $UserVerifyCodeModel->createVerifyCode(UserVerifyCodeModel::TYPE_RESET_PASSWORD, $email, $code, 15 * 60);
        if (!$verifyCode) {
            $this->error = '重置验证码生成失败!';
            return false;
        }

        $url = '';//重置密码url
        $subject = '重置密码';
        $message = get_config('email_reset_password');
        $data = [
            'code' => $code,
            'url'  => $url
        ];
        $res = send_mail($email, $subject, $message, true, $data);

        $expires = strtotime(date('Y-m-d', strtotime('+1 day'))) - time(); //第二天0点过期
        cache($mark, cache($mark) + 1, $expires);

        return $res;
    }

    /**
     * 发送重置短信验证码，条件：1、配置短信通道；
     * @param $mobile
     * @return bool|\mix
     * @throws Exception
     */
    public function sendResetSms($mobile)
    {
        if (!PregUtil::isMobile($mobile)) {
            $this->error = '手机格式不正确';
            return false;
        }

        $channel = get_config('sms_channel');
        if (empty($channel)) {
            throw new Exception("未配置短信通道");
        }

        throw new Exception("短信发送正在实现中...");
    }

    /**
     * 核对验证码；
     * @param $type
     * @param $target
     * @param $code
     * @return bool
     */
    public function checkVerifyCode($type, $target, $code)
    {
        $UserVerifyCodeModel = new UserVerifyCodeModel();
        $userVerifyCode = $UserVerifyCodeModel->findLatestByTarget($type, $target);
        if (!$userVerifyCode || $userVerifyCode['code'] != $code) {
            $this->error = '验证码不正确!';
            return false;
        }

        if ($userVerifyCode['status'] == UserVerifyCodeModel::STATUS_USED) {
            $this->error = '验证码已使用!';
            return false;
        }

        $currentTime = time();
        $expireTime = strtotime($userVerifyCode->expire_time);
        if ($currentTime > $expireTime) {
            $this->error = '验证码已过期!';
            return false;
        }

        return true;
    }

    /**
     * 消费验证码
     * @param $type
     * @param $target
     * @param $code
     * @return bool
     */
    public function consumeCode($type, $target, $code)
    {
        $UserVerifyCodeModel = new UserVerifyCodeModel();
        $userVerifyCode = $UserVerifyCodeModel->findLatestByTarget($type, $target);
        if (!$userVerifyCode || $userVerifyCode['code'] != $code) {
            $this->error = '验证码不正确!';
            return false;
        }

        $codeId = $userVerifyCode['id'];
        $res = $UserVerifyCodeModel->setCodeUsed($codeId);

        return $res;
    }

//    public function checkInviteCode($inviteCode)
//    {
//        if (empty($code)) {
//            $this->error = '邀请码不能为空！';
//        }
//
//        $userInviteCode = model('UserInviteCode')->getByCode($inviteCode);
//        if (!$userInviteCode) {
//            $this->error = '邀请码不正确!';
//            return false;
//        }
//
//        if ($userInviteCode['status'] == UserInviteCode::STATUS_USED) {
//            $this->error = '邀请码已经被使用!';
//            return false;
//        }
//        if (time() > strtotime($userInviteCode['expire_time'])) {
//            $this->error = '邀请码已过期!';
//            return false;
//        }
//
//        return true;
//    }
//
//    public function generateInviteCode($userId, $count)
//    {
//        $data = [
//            'user_id' => $userId,
//            'status' => UserInviteCode::STATUS_UNUSED,
//            'code' => '',
//            'expire_time' => date('Y-m-d H:i:s', strtotime("+365 day")),
//        ];
//
//        for ($i = 0; $i < $count; $i++) {
//            $code = StringUtil::getRandString(8);
//            $data['code'] = $code;
//            model('UserInviteCode')->insert($data);
//        }
//
//        return true;
//    }
}