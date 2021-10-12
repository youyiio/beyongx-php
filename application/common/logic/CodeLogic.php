<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2016/10/8
 * Time: 13:58
 */
namespace app\common\logic;

use think\Exception;
use think\Model;
use app\common\model\UserModel;
use think\facade\Cache;

use beyong\commons\utils\PregUtils;
use beyong\commons\utils\StringUtils;
use LogicException;

/**
 * 验证码管理
 */
class CodeLogic extends Model
{

    const STATUS_UNUSED = 1; //未使用
    const STATUS_USED = 2;  //已使用

    const TYPE_REGISTER = 'register';
    const TYPE_LOGIN = 'login';
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
            throw new LogicException("邮箱不存在！");
            return false;
        }

        $code = StringUtils::getRandString(12);
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
     * 发送注册邮箱验证码
     * @param $email
     * @throws Exception
     */
    public function sendRegisterCodeByEmail($email)
    {
        $UserModel = new UserModel();
        $user = $UserModel->findByEmail($email);
        if ($user) {
            //$this->error = '邮箱已经注册！';
            throw new LogicException("邮箱已经注册！");
            return false;
        }

        $code = StringUtils::getRandNum(6);

        $url = url('frontend/Index/index', '', false, get_config('domain_name'));

        $subject = '新用户注册';
        $message = "您的注册验证码：{code}，10分钟内有效! <a href=\"{url}\">{site_name}</a>";
        $data = [
            'code' => $code,
            'url' => $url,
            'site_name' => get_config('site_name')
        ];

        $res = send_mail($email, $subject, $message, true, $data);
        if ($res !== true) {
            throw new LogicException($res);
        }

        // 缓存验证码
        Cache::set(self::TYPE_REGISTER . CACHE_SEPARATOR . $email, $code, 10 * 60);
        
        return true;
    }

    /**
     * 发送注册短信验证码，条件：1、配置短信通道；
     * @param $mobile
     * @throws Exception
     */
    public function sendRegisterCodeByMobile($mobile)
    {
        $UserModel = new UserModel();
        $user = $UserModel->findByMobile($mobile);
        if ($user) {
            //$this->error = '手机已经注册！';
            throw new LogicException("手机已经注册！");
            return false;
        }

        $smsConfig = config('sms.');
        $action = self::TYPE_REGISTER;
        if (!isset($smsConfig["actions"][$action])) {
            throw new LogicException("短信action类型不支持或者未配置!");
        }

        \beyong\sms\Config::init($smsConfig);

        $client = \beyong\sms\SmsClient::instance();
        
        //$sign、$template和$templateParams 服务商控制台获取
        $sign = $smsConfig['actions'][$action]['sign'];
        $template = $smsConfig['actions'][$action]['template'];

        $code = StringUtils::getRandNum(6);
        $templateParams = ['code' => $code];
        
        $response = $client->to($mobile)->sign($sign)->template($template, $templateParams)->send();
        if ($response !== true) {            
            throw new LogicException($client->getError());
        }

        Cache::set($action . CACHE_SEPARATOR . $mobile, $code, 5 * 60);

        return true;
    }

    /**
     * 发送短信验证码，条件：1、配置短信通道；
     * @param $mobile
     * @throws Exception
     */
    public function sendCodeByMobile($mobile, $action)
    {
        $smsConfig = config('sms.');
        if (!isset($smsConfig["actions"][$action])) {
            throw new LogicException("短信action类型不支持或者未配置!");
        }
        
        \beyong\sms\Config::init($smsConfig);

        $client = \beyong\sms\SmsClient::instance();
        
        //$sign、$template和$templateParams 服务商控制台获取
        $sign = $smsConfig['actions'][$action]['sign'];
        $template = $smsConfig['actions'][$action]['template'];

        $code = StringUtils::getRandNum(6);
        $templateParams = ['code' => $code];
        
        $response = $client->to($mobile)->sign($sign)->template($template, $templateParams)->send();
        if ($response !== true) {            
            throw new LogicException($client->getError());
        }

        Cache::set($action . CACHE_SEPARATOR . $mobile, $code, 5 * 60);

        return true;
    }

    /**
     * 发送重置的邮箱验证码
     * @param $email
     * @return bool|\mix
     * @throws \mailer\lib\Exception
     */
    public function sendResetCodeEmail($email)
    {
        if (!PregUtils::isEmail($email)) {
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

        $code = StringUtils::getRandString(12);
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
    public function sendResetCodeByMobile($mobile)
    {
        $UserModel = new UserModel();
        $user = $UserModel->findByMobile($mobile);
        if (!$user) {
            throw new LogicException("手机号不正确！");
            return false;
        }

        $smsConfig = config('sms.');
        $action = self::TYPE_RESET_PASSWORD;
        if (!isset($smsConfig["actions"][$action])) {
            throw new LogicException("短信action类型不支持或者未配置!");
        }
        
        \beyong\sms\Config::init($smsConfig);

        $client = \beyong\sms\SmsClient::instance();
        
        //$sign、$template和$templateParams 服务商控制台获取
        $sign = $smsConfig['actions'][$action]['sign'];
        $template = $smsConfig['actions'][$action]['template'];

        $code = StringUtils::getRandNum(6);
        $templateParams = ['code' => $code];
        
        $response = $client->to($mobile)->sign($sign)->template($template, $templateParams)->send();
        if ($response !== true) {            
            throw new LogicException($client->getError());
        }

        Cache::set($action . CACHE_SEPARATOR . $mobile, $code, 5 * 60);

        return true;
    }

    /**
     * 核对验证码；
     * @param $type
     * @param $target
     * @param $code
     * @return bool
     */
    public function checkCode($type, $username, $code)
    {
        $cacheCode = Cache::get($type . CACHE_SEPARATOR . $username, null);
        if ($cacheCode === null) {
            $this->error = '验证码已过期!';
            return false;
        }
        if ($cacheCode == null || $cacheCode !== $code) {
            $this->error = '验证码不正确!';
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
    public function consumeCode($type, $username, $code)
    {
        $cacheCode = Cache::get($type . CACHE_SEPARATOR . $username, null);
        if ($cacheCode == null || $cacheCode !== $code) {
            $this->error = '验证码不正确或已过期!';
            return false;
        }

        Cache::rm($type . CACHE_SEPARATOR . $username);

        return true;
    }

}