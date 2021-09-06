<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2016/10/8
 * Time: 13:58
 */
namespace app\common\logic;

use think\Exception;
use think\facade\Env;
use think\Model;
use app\common\model\UserModel;
use think\facade\Cache;
use think\facade\Config;

use youyi\util\PregUtil;
use youyi\util\StringUtil;

/**
 * 验证码管理
 */
class CodeLogic extends Model
{

    const STATUS_UNUSED = 1; //未使用
    const STATUS_USED = 2;  //已使用

    const TYPE_REGISTER = 'register';
    const TYPE_RESET_PASSWORD = 'reset_password';
    const TYPE_MAIL_ACTIVE = 'mail_active';

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
        // 缓存验证码
        Cache::set(self::TYPE_MAIL_ACTIVE . CACHE_SEPARATOR . $email, $code, 24 * 60 * 60);
        

        $url = url($activeAction, ['code' => $code, 'email' => $email], false, true);

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

        $mark = $user['id'] . '_send_reset_count';
        if (cache($mark) >= 5 && !config('app_debug')) {
            $this->error = '您今天请求重置码次数已经超限!';
            return false;
        }

        $code = StringUtil::getRandString(12);
        // 缓存验证码
        Cache::set(self::TYPE_RESET_PASSWORD . CACHE_SEPARATOR . $email, $code, 24 * 60 * 60);

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
        $cacheCode = Cache::get($type . CACHE_SEPARATOR . $target, null);
        if ($cacheCode == null || $cacheCode !== $code) {
            $this->error = '验证码不正确或已过期!';
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
        $cacheCode = Cache::get($type . CACHE_SEPARATOR . $target, null);
        if ($cacheCode == null || $cacheCode !== $code) {
            $this->error = '验证码不正确或已过期!';
            return false;
        }

        Cache::rm($type . CACHE_SEPARATOR . $target);

        return true;
    }

}