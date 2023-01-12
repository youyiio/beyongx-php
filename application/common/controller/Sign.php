<?php
namespace app\common\controller;

use think\facade\Cache;
use think\Controller;
use think\facade\Session;

use think\captcha\Captcha;

use app\common\model\ActionLogModel;
use app\common\model\UserModel;

use app\common\logic\CodeLogic;
use app\common\logic\UserLogic;
use app\common\logic\ActionLogLogic;

use beyong\commons\utils\StringUtils;
use beyong\commons\utils\PregUtils;

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
        'register_code_type' => 'email', //注册码方式，值为：email,mobile
        'reset_enable' => false, //忘记密码功能是否支持
        'reset_code_type' => 'email', //重置密码，值为：email,mobile
    ];

    public function initialize()
    {
        parent::initialize();

        $config = config('sign.');

        $this->defaultConfig = array_merge($this->defaultConfig, $config);

        $this->view->engine->layout(false);
    }

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
        $tryLoginCountMark = $username . '_try_login_count';
        $tryLoginCount = Cache::get($tryLoginCountMark);
        if ($tryLoginCount > 5) {
            $this->error('登录错误超过5次,账号被临时冻结1天');
        }
        if ($tryLoginCount >= 5) {
            Cache::set($tryLoginCountMark, $tryLoginCount + 1, strtotime(date('Y-m-d 23:59:59'))-time());
            
            $this->error('登录错误超过5次,账号被临时冻结1天');
        }

        //初始化登录错误次数
        Cache::remember($tryLoginCountMark, function () {
            return 0;
        }, strtotime(date('Y-m-d 23:59:59')) - time());

        try {
            $UserLogic = new UserLogic();
            $user = $UserLogic->login($username, $password, request()->ip(0, true));
            if (!$user) {                
                Cache::inc($tryLoginCountMark);

                $this->error($UserLogic->getError());
            }
        } catch(\Exception $e) {            
            Cache::inc($tryLoginCountMark);

            throw $e;
        }

        //登录成功清除
        Cache::rm($tryLoginCountMark);

        $uid = $user['id'];
        //登录日志
        $ActionLogLogic = new ActionLogLogic();
        $ActionLogLogic->addLog($uid, ActionLogModel::ACTION_LOGIN, '登录');

        $expire = config('session.expire');//缓存期限
        session('uid', $uid);
        cookie('uid', $uid, $expire);

        //用于实现用户单个端登录；
        if ($this->defaultConfig['login_multi_client_support'] !== true) {
            //生成login_hash
            $loginHash = uniqid(rand(1000000, 99999999));
            cookie($uid . CACHE_SEPARATOR . 'login_hash', $loginHash);
            cache($uid . CACHE_SEPARATOR . 'login_hash', $loginHash);
        }

        cookie('username', $username, 3600 * 24 * 15);  //保存用户名在cookie

        $loginSuccessView = url($this->defaultConfig['login_success_view']);
        if (input('redirect')) {
            $loginSuccessView = urldecode(input('redirect'));
        }
        $this->success('登陆成功', $loginSuccessView);
    }

    /**
     * 注册页面
     *  params required: username,password,repassword,code,nickname
     *  params optional:
     */
    public function register()
    {
        if ($this->defaultConfig['register_enable'] !== true) {
            $this->error('暂不提供注册功能，请联系管理员！');
        }

        //网页展示
        if (!$this->request->isAjax()) {
            return $this->fetch('register');
        }
        
        $data = input('post.');
        $check = $this->validate($data, 'User.register');
        if ($check !== true) {
            $this->error($check);
        }

        $username = $data['username'];
        $code = $data["code"];

        //验证码验证
        $codeLogic = new CodeLogic();
        if (PregUtils::isMobile($username)) {
            $check = $codeLogic->checkCode(CodeLogic::TYPE_REGISTER, $username, $code);
            if ($check !== true) {
                $this->error($codeLogic->getError());
            }
        } else if (PregUtils::isEmail($username)) {
            $check = $codeLogic->checkCode(CodeLogic::TYPE_REGISTER, $username, $code);
            if ($check !== true) {
                $this->error($codeLogic->getError());
            }
        }

        //推荐人判断处理
        $invite = input('param.invite', '');
        if (empty($invite)) {
            $data['referee'] = 1; //推荐人
        } else {
            $UserModel = new UserModel();
            $data['referee'] = $UserModel->where('account', $invite)->value('id');
        }

        //确认注册各字段
        $mobile = StringUtils::getRandNum(11);
        $email = $mobile .'@' . StringUtils::getRandString(12) . '.com';
        if (PregUtils::isMobile($username)) {
            $mobile = $username;
        } else if (PregUtils::isEmail($username)) {
            $email = $username;
        }
        
        $nickname = isset($data['nickname']) ? $data['nickname'] : '用户' . substr($mobile, 5);

        $userLogic = new UserLogic();
        $user  = $userLogic->register($mobile, $data['password'], $nickname, $email, '', UserModel::STATUS_ACTIVED);
        if (!$user) {
            $this->error($userLogic->getError());
        }
        
        //消耗掉验证码
        $codeLogic->consumeCode(CodeLogic::TYPE_REGISTER, $username, $code);

        $UserModel = new UserModel();
        //完善用户资料
        $profileData = [
            'id' => $user['id'],
            'head_url' => '/static/common/img/head/default.jpg',
            'referee' => $data['referee'], //推荐人
            'register_ip' => request()->ip(0, true),
            'from_referee' => sub_str(cookie('from_referee'), 0, 250),
            'entrance_url' => sub_str(cookie('entrance_url'), 0, 250)
        ];
        $UserModel->where('id', $user['id'])->setField($profileData);

        //资料扩展
        if (PregUtils::isMobile($username)) {
            $user->meta('mobile_verified', '1');
        } else if (PregUtils::isEmail($username)) {
            $user->meta('email_verified', '1');
        }

        //注册的后置操作
        $this->afterRegister($user['id']);

        //注册成功，调整登录页面
        $this->success("恭喜您，账号注册成功！", url(request()->module() . '/Sign/login'));
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
     * 发送邮箱或短信验证码
     *
     * @return void
     */
    public function sendCode()
    {
        if ($this->request->method() != 'POST') {
            $this->error('非法访问！请检查请求方式！');
        }
        if (!in_array($this->defaultConfig["register_code_type"], ["email", "mobile"])) {
            $this->error('注册方式 register_code_type 配置不正确！');
        }

        $params = input('post.');
        $username = $params["username"];
        $action = isset($params["action"]) ? $params["action"] : "login";
        if ($this->defaultConfig["register_code_type"] == "mobile" && !PregUtils::isMobile($username)) {
            $this->error("手机号格式不正确!");
        }
        if ($this->defaultConfig["register_code_type"] == "email" && !PregUtils::isEmail($username)) {
            $this->error("邮箱格式不正确!");
        }

        // 防止短信被刷,频率限制验证
        $usernameFrequency = Cache::get($username . "_send_code_frequency");
        $ipFrequency = Cache::get($this->request->ip(0, true) . "_send_code_frequency");
        $frequencyLimited = !empty($usernameFrequency) || !empty($ipFrequency);
        if ($frequencyLimited) {
            return $this->error("您操作过于频繁，请稍候再试!");
        }

        //验证码发送
        try {
            $codeLogic = new CodeLogic();
            $res = false;
            if ($this->defaultConfig["register_code_type"] == "mobile") {
                $res = $codeLogic->sendRegisterCodeByMobile($username);
            } else if ($this->defaultConfig["register_code_type"] == "email") {
                $res = $codeLogic->sendRegisterCodeByEmail($username);                
            }
            if ($res !== true) {
                $this->error($codeLogic->getError());
            }
        } catch(\Exception $e) {
            $this->error($e->getMessage());
        }

        //频率限制
        Cache::set($username . "_send_code_frequency", '60s', 60);
        Cache::set($this->request->ip(0, true) . "_send_code_frequency", '20s', 20);

        $this->success("验证码发送成功!");
    }

    /**
     * 发送重置验证码
     */
    public function resetCode()
    {
        if ($this->defaultConfig['reset_enable'] !== true) {
            $this->error('暂不提供此功能，请联系管理员！');
        }

        if (!$this->request->isAjax()) {
            $this->error('请求方式错误！');
        }

        
        $username = input('post.username', '');

        $CodeLogic = new CodeLogic();
        if ($this->defaultConfig['reset_code_type'] === 'email') {
            //发送重置邮件
            $res = $CodeLogic->sendResetCodeByEmail($username);
            if ($res) {
                $this->success('验证码已发生到您的邮箱!', url('Sign/reset', ['username' => $username]));
            } else {
                $this->error($CodeLogic->getError());
            }
        } else if ($this->defaultConfig['reset_code_type'] === 'mobile') {
            //发送重置短信
            $res = $CodeLogic->sendResetCodeByMobile($username);
            if ($res) {
                $this->success('验证码短信已发送到您的手机!', url('Sign/reset', ['username' => $username]));
            } else {
                $this->error($CodeLogic->getError());
            }
        } else {
            $this->error('不支持的重置密码发送方式！');
        }
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
        if ($this->defaultConfig['reset_code_type'] === 'email') {
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
            $uid = $user['id'];

            $CodeLogic = new CodeLogic();
            if (!$CodeLogic->checkCode(CodeLogic::TYPE_RESET_PASSWORD, $username, $code)) {
                $this->error($CodeLogic->getError());
            }

            $UserModel = new UserModel();
            $res = $UserModel->modifyPassword($uid, $password);
            if ($res) {
                //消费验证码
                $CodeLogic->consumeCode(CodeLogic::TYPE_RESET_PASSWORD, $username, $code);

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
            $this->error('错误：参数错误！', url('frontend/Index/index'));
        }

        $CodeLogic = new CodeLogic();
        $check = $CodeLogic->checkCode(CodeLogic::TYPE_MAIL_ACTIVE, $email, $code);
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
        $UserModel->where('id', $user['id'])->setField('status', UserModel::STATUS_ACTIVED);
        //消费验证码
        $CodeLogic->consumeCode(CodeLogic::TYPE_REGISTER, $email, $code);

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
        cookie($uid . CACHE_SEPARATOR . 'login_hash',null);

        //清理相关缓存
        cache($uid . '_menu', null);
        cache($uid . CACHE_SEPARATOR . 'login_hash', null);

        $this->redirect($this->defaultConfig['logout_success_view']);
    }

}
