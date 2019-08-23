<?php
namespace app\common\controller;

use app\common\logic\CodeLogic;
use app\common\model\UserVerifyCodeModel;
use think\facade\Cache;
use think\Controller;
use think\facade\Session;

use think\captcha\Captcha;

use app\common\model\ActionLogModel;
use app\common\model\UserModel;

use app\common\logic\UserLogic;
use app\common\logic\ActionLogLogic;
use youyi\util\StringUtil;

/**
 * 登录/注册/帐号处理控制器
 */
class Sign extends Controller
{
    protected $defaultConfig = [
        'login_multi_client_support' => false, //支持单个用户多个端同时登录
        'login_success_view' => '', //登录成功后，跳转地址
        'logout_success_view' => '', //注销后，跳转地址
        'register_enable' => false, //注册功能是否支持
        'register_code_type' => 'mail', //注册码方式，值为：mail,mobile
        'reset_enable' => false, //忘记密码功能是否支持
        'reset_code_type' => 'mail', //重置密码，值为：mail,mobile
    ];

    /**
     * 登录页面
     */
    public function index()
    {
        return $this->fetch('login');
    }

    /**
     * 验证码，独立设置防止各模块使用独立子域名时，验证不正确；
     * html 中，src="{:captcha_src()}" 会默认使用cms的session
     * @return mixed
     */
    public function captcha()
    {
        $captcha = new Captcha(config('captcha.'));
        return $captcha->entry();
    }

    //登录处理
    public function login()
    {
        if (!request()->isAjax()) {
            return $this->fetch('login');
        }

        $result = $this->validate(input('param.'), 'User.login');
        if (true !== $result) {
            $this->error($result);
        }

        $username = input('param.username');
        $password = input("param.password");

        //登录次数判断
        $logErrorMark = input('username') . '_login_error';
        $logErrorCount = Cache::get($logErrorMark);
        if ($logErrorCount > 5) {
            $this->error('登录错误超过5次,账号被临时冻结1天');
        }
        if ($logErrorCount >= 5) {
            Cache::set($logErrorMark, $logErrorCount + 1, strtotime(date('Y-m-d 23:59:59'))-time());
            $this->error('登录错误超过5次,账号被临时冻结1天');
        }

        $userLogic = new UserLogic();
        $user = $userLogic->login($username, $password, request()->ip(0, true));
        if (!$user) {
            Cache::remember($logErrorMark, function() {
                return 0;
            },strtotime(date('Y-m-d 23:59:59'))-time());
            Cache::inc($logErrorMark);

            $this->error($userLogic->getError());
        }

        $uid = $user['user_id'];
        //登录日志
        $actionLog = new ActionLogLogic();
        $actionLog->addLog($uid, ActionLogModel::ACTION_LOGIN, '登录');

        $expire = config('session.expire');//缓存期限
        session('uid', $uid);
        cookie('uid', $uid, $expire);

        //用于实现用户单个端登录；
        if ($this->defaultConfig['login_multi_client_support'] !== true) {
            //生成login_hash
            $loginHash = uniqid(rand(1000000, 99999999));
            cookie($uid . '_login_hash', $loginHash);
            cache($uid . '_login_hash', $loginHash);
        }

        cookie('username', $username, 3600 * 24 * 15);  //保存用户名在cookie

        $loginSuccessView = $this->defaultConfig['login_success_view'];
        if (input('redirect')) {
            $loginSuccessView = urldecode(input('redirect'));
        }
        $this->success('登陆成功', $loginSuccessView);
    }

    /**
     * 注册页面
     */
    public function register()
    {
        if ($this->defaultConfig['register_enable'] !== true) {
            $this->error('暂不提供注册功能，请联系管理员！');
        }

        if (request()->isAjax()) {
            $data  = input('post.');
            $check = $this->validate($data, 'User.register');
            if ($check !== true) {
                $this->error($check);
            }

            //推荐人判断处理
            $invite = input('param.invite', '');
            if (empty($invite)) {
                $data['referee'] = 1; //推荐人
            } else {
                $UserModel = new UserModel();
                $data['referee'] = $UserModel->where('account', $invite)->value('user_id');
            }

            $userLogic = new UserLogic();
            $mobile = StringUtil::getRandNum(11);
            $user  = $userLogic->register($mobile, $data['password'], $data['nickname'], $data['email'], '', UserModel::STATUS_APPLY);
            if ($user) {
                $UserModel = new UserModel();
                //完善用户资料
                $profileData = [
                    'user_id' => $user['user_id'],
                    'head_url' => '/static/cms/image/head/0002.jpg',
                    'referee' => $data['referee'], //推荐人
                    'register_ip' => request()->ip(0, true),
                    'from_referee' => cookie('from_referee'),
                    'entrance_url'     => cookie('entrance_url'),
                ];
                $UserModel->where('user_id', $user['user_id'])->setField($profileData);

                //资料扩展
                //$UserModel->ext('xxx', 'vvv');
                //$UserModel->meta('xxx', 'vvv');

                //注册的后置操作
                $this->afterRegister($user['user_id']);

                //发送激活邮件
                $CodeLogic = new CodeLogic();
                $res = $CodeLogic->sendActiveMail($user['email'], request()->module() . '/Sign/mailActive');
                if ($res) {
                    $param = ['uid'=>$user['user_id'], 'email'=>$user['email']];
                    $this->success('注册成功, 请登录邮箱激活您的帐号!', url(request()->module() . '/Sign/login'), $param);
                } else {
                    $this->success('注册成功, 邮件发送失败!', url(request()->module() . '/Sign/login'), ['uid'=>$user['user_id'], 'email'=>$user['email']]);
                }

            } else {
                $this->error($userLogic->getError());
            }
        }

        return $this->fetch('register');
    }

    /**
     * 注册的后置操作
     * @param $uid
     * @return mixed
     */
    protected function afterRegister($uid)
    {

    }

    /**
     * 忘记密码
     */
    public function forget()
    {
        if ($this->defaultConfig['reset_enable'] !== true) {
            $this->error('暂不提供此功能，请联系管理员！');
        }

        if (request()->isAjax()) {
            $username = input('post.username', '');

            $CodeLogic = new CodeLogic();
            if ($this->defaultConfig['reset_code_type'] === 'mail') {
                //发送重置邮件
                $res = $CodeLogic->sendResetMail($username);
                if ($res) {
                    $this->success('验证码已发生到您的邮箱!', url('Sign/reset', ['username' => $username]));
                } else {
                    $this->error($CodeLogic->getError());
                }
            } else if ($this->defaultConfig['reset_code_type'] === 'mobile') {
                //发送重置短信
                $res = $CodeLogic->sendResetSms($username);
                if ($res) {
                    $this->success('验证码短信已发送到您的手机!', url('Sign/reset', ['username' => $username]));
                } else {
                    $this->error($CodeLogic->getError());
                }
            } else {
                $this->error('不支持的重置密码发送方式！');
            }
        }

        return $this->fetch('forget');
    }

    /**
     * 重置密码
     */
    public function reset()
    {
        if ($this->defaultConfig['reset_enable'] !== true) {
            $this->error('暂不提供此功能，请联系管理员！');
        }

        $username = input('username', '');
        $code = input('code', '');

        //验证账号是否存在
        $UserModel = new UserModel();
        $user = null;
        if ($this->defaultConfig['reset_code_type'] === 'mail') {
            $user = $UserModel->findByEmail($username);
        } else if ($this->defaultConfig['reset_code_type'] === 'mobile') {
            $user = $UserModel->findByMobile($username);
        } else {
            $this->error('不支持的重置密码发送方式！');
        }

        if (!$user) {
            $this->error('用户不存在');
        }

        if (request()->isAjax()) {
            $check = $this->validate(input('post.'), 'User.resetPwd');
            if ($check !== true) {
                $this->error($check);
            }

            $password = input('post.password');
            $uid = $user['user_id'];

            $CodeLogic = new CodeLogic();
            if (!$CodeLogic->checkVerifyCode(UserVerifyCodeModel::TYPE_RESET_PASSWORD, $username, $code)) {
                $this->error($CodeLogic->getError());
            }

            $UserModel = new UserModel();
            $res = $UserModel->modifyPassword($uid, $password);
            if ($res) {
                //消费验证码
                $CodeLogic->consumeCode(UserVerifyCodeModel::TYPE_RESET_PASSWORD, $username, $code);

                $this->success('成功重置密码', url('Sign/login'));
            } else {
                $this->error('密码重置失败');
            }
        }

        $this->assign('username', $username);
        $this->assign('code', $code);

        return $this->fetch('reset');
    }

    //邮件激活
    public function mailActive()
    {
        $code = input('param.code/s');
        $email = input('param.email/s');
        if (empty($code) || empty($email)) {
            $this->error('错误：参数错误！', url('cms/Index/index'));
        }

        $CodeLogic = new CodeLogic();
        $check = $CodeLogic->checkVerifyCode(UserVerifyCodeModel::TYPE_MAIL_ACTIVE, $email, $code);
        if (!$check) {
            $this->error($CodeLogic->getError());
        }

        $UserModel = new UserModel();
        $user = $UserModel->findByEmail($email);
        if (!$user) {
            $this->error('邮箱不存在', url(request()->module() . '/Sign/register'));
        }
        if ($user['status'] == UserModel::STATUS_ACTIVED) {
            $this->success('邮箱已激活过，无需重新激活！', url(request()->module() . '/Sign/login'));
        }

        //激活用户
        $UserModel->where('user_id', $user['user_id'])->setField('status', UserModel::STATUS_ACTIVED);
        //消费验证码
        $CodeLogic->consumeCode(UserVerifyCodeModel::TYPE_REGISTER, $email, $code);

        //邮件激活的后置操作
        $this->afterMailActive($email);

        return $this->fetch('mailActive');
    }

    /**
     * 邮件激活的后置操作
     * @param $email
     * @return mixed
     */
    protected function afterMailActive($email)
    {

    }

    //登出处理
    public function logout()
    {
        $uid = session('uid');
        $actionLog = new ActionLogLogic();
        $actionLog->addLog($uid, ActionLogModel::ACTION_LOGOUT, '登出 ');

        $uid = session('uid');

        //清除session
        Session::clear();
        session('uid', null);

        //清除cookie
        cookie('uid', null);
        cookie($uid . '_login_hash',null);

        //清理相关缓存
        cache($uid . '_menu', null);
        cache($uid . '_login_hash', null);

        $this->redirect($this->defaultConfig['logout_success_view']);
    }

}
