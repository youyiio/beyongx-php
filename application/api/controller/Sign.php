<?php
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\logic\ActionLogLogic;
use app\common\logic\UserLogic;
use app\common\model\AuthGroupAccessModel;
use app\common\model\UserModel;
use Firebase\JWT\JWT;
use think\facade\Cache;
use beyong\commons\utils\PregUtils;
use beyong\commons\utils\StringUtils;
use app\common\logic\CodeLogic;

use app\common\model\ActionLogModel;
use app\common\model\UserRoleModel;
use think\facade\Session;

class Sign extends Base
{
    protected $defaultConfig = [
        'login_multi_client_support' => false, //支持单个用户多个端同时登录
        'login_success_view' => '', //登录成功后，跳转地址
        'logout_success_view' => '', //注销后，跳转地址
        'register_enable' => false, //注册功能是否支持
        'register_code_type' => '', //注册码方式，值为：email,mobile
        'reset_enable' => false, //忘记密码功能是否支持
        'reset_code_type' => '', //重置密码，值为：email,mobile
    ];

    public function initialize()
    {
        parent::initialize();

        $config = config('sign.');

        $this->defaultConfig = array_merge($this->defaultConfig, $config);

    }
    
    // 注册
    public function register()
    {
        if ($this->request->method() != 'POST') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }
        
        //请求的body数据
        $params = $this->request->put();

        $check = validate('Sign')->scene('register')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('Sign')->getError());
        }

        $code = $params["code"];
        $username = $params['username'];

        //验证码验证
        $codeLogic = new CodeLogic();
        if (PregUtils::isMobile($username)) {
            $check = $codeLogic->checkCode(CodeLogic::TYPE_REGISTER, $username, $code);
            if ($check !== true) {
                return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, $codeLogic->getError());
            }
        } else if (PregUtils::isEmail($username)) {
            $check = $codeLogic->checkCode(CodeLogic::TYPE_REGISTER, $username, $code);
            if ($check !== true) {
                return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, $codeLogic->getError());
            }
        }
       
        //消费验证码
        $codeLogic->consumeCode(CodeLogic::TYPE_RESET_PASSWORD, $username, $code);
       
        //确认注册各字段
        $mobile = StringUtils::getRandNum(11);
        $email = $mobile .'@' . StringUtils::getRandString(6) . '.com';
        if (PregUtils::isMobile($params['username'])) {
            $mobile = $params['username'];
        } else if (PregUtils::isEmail($params['username'])) {
            $email = $params['username'];
        }
        
        $nickname = isset($params['nickname']) ? $params['nickname'] : '用户' . substr($mobile, 5);

        $UserLogic = new UserLogic();
        $user = $UserLogic->register($mobile, $params['password'], $nickname, $email, '', UserModel::STATUS_ACTIVED);
        if (!$user) {
            return ajax_error(ResultCode::E_LOGIC_ERROR, $UserLogic->getError());
        }

        $UserModel = new UserModel();
        //完善用户资料
        $profileData = [
            'id' => $user['id'],
            'head_url' => '/static/common/img/head/default.jpg',
            'referee' => 1, //$data['referee'], //推荐人
            'register_ip' => request()->ip(0, true),
            'from_referee' => cookie('from_referee'),
            'entrance_url'     => cookie('entrance_url'),
        ];
        $UserModel->where('id', $user['id'])->setField($profileData);

        //权限初始化
        $userRole[] = [
            'uid' => $user->id,
            'role_id' => config('user_group_id')
        ];
        $UserRoleModel = new UserRoleModel();
        $UserRoleModel->insertAll($userRole);

        $returnData = [
            'uid' => $user->id,
            'account' => $user->account,
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'status' => $user->status,
            'registerTime' => $user->register_time
        ];

        return ajax_return(ResultCode::ACTION_SUCCESS, '注册成功', $returnData);
    }

    //登录
    public function login()
    {
        if ($this->request->method() != 'POST') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        //请求的body数据
        $params = $this->request->put();

        $check = validate('Sign')->scene('login')->check($params);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, validate('Sign')->getError());
        }

        //登录次数判断
        $tryLoginCountMark = $params['username'] . '_try_login_count';
        $tryLoginCount = Cache::get($tryLoginCountMark);
        if ($tryLoginCount > 5) {
           return ajax_error(ResultCode::E_USER_STATE_FREED, '登录错误超过5次,账号被临时冻结1天');
        }
        if ($tryLoginCount >= 5) {
           Cache::set($tryLoginCountMark, $tryLoginCount + 1, strtotime(date('Y-m-d 23:59:59'))-time());
           
           return ajax_error(ResultCode::E_USER_STATE_FREED, '登录错误超过5次,账号被临时冻结1天');
        }

        //初始化登录错误次数
        Cache::remember($tryLoginCountMark, function () {
            return 0;
        }, strtotime(date('Y-m-d 23:59:59')) - time());

        try {
            $UserLogic = new UserLogic();
            $user = $UserLogic->login($params['username'], $params['password'], request()->ip(0, true));
            if (!$user) {                
                Cache::inc($tryLoginCountMark);

                return ajax_error(ResultCode::E_UNKNOW_ERROR, $UserLogic->getError());
            }
        } catch(\Exception $e) {            
            Cache::inc($tryLoginCountMark);

            throw $e;
        }

        //登录成功清除
        Cache::rm($tryLoginCountMark);     

        $uid = $user['id'];
        $ActionLogLogic = new ActionLogLogic();
        $ActionLogLogic->addLog($uid, \ApiCode::E_USER_LOGIN_ERROR, '登录');

        $payload = [
            'iss' => 'jwt_admin',  //该JWT的签发者
            'aud' => 'jwt_api', //接收该JWT的一方，可选
            'iat' => time(),  //签发时间
            'exp' => time() + config('jwt.jwt_expired_time'),  //过期时间
//            'nbf' => time() + 60,  //该时间之前不接收处理该Token
            'sub' => 'domain',  //面向的用户
            'jti' => md5(uniqid('JWT') . time()),  //该Token唯一标识
            'data' => [
                'uid' => $uid,
            ]
        ];
        $token = JWT::encode($payload, config('jwt.jwt_key'), config('jwt.jwt_alg'));

        $data = [
            'token' => 'Bearer ' . $token
        ];

        return ajax_success($data);
    }

    //注销登录
    public function logout()
    {
        $payloadData = session('jwt_payload_data');
        if (!$payloadData) {
            return ajax_error(ResultCode::ACTION_FAILED, 'TOKEN自定义参数不存在！');
        }
        $uid = $payloadData->uid;
        if (!$uid) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }

        $actionLog = new ActionLogLogic();
        $actionLog->addLog($uid, ActionLogModel::ACTION_LOGOUT, '登出 ');

        $uid = session('uid');

        //清除session
        Session::clear();
        Session::delete('uid');

        //清除cookie
        cookie('uid', null);
        cookie($uid . CACHE_SEPARATOR . 'login_hash',null);

        //清理相关缓存
        cache($uid . '_menu', null);
        cache($uid . CACHE_SEPARATOR . 'login_hash', null);

        return ajax_success(null);
    }

    /**
     * 忘记密码,重置密码
     */
    public function reset()
    {
        if ($this->request->method() != 'POST') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        if ($this->defaultConfig['reset_enable'] !== true) {
            return ajax_error(ResultCode::SC_FORBIDDEN, '暂不提供此功能，请联系管理员！');
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
            return ajax_error(ResultCode::ACTION_FAILED, '不支持的重置密码发送方式！');
        }

        if (!$user) {
            return ajax_error(ResultCode::ACTION_FAILED, '用户不存在');
        }
        
        $check = $this->validate(input('post.'), 'User.resetPwd');
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, $check);
        }

        $password = input('post.password');
        $uid = $user['id'];

        $CodeLogic = new CodeLogic();
        if (!$CodeLogic->checkCode(CodeLogic::TYPE_RESET_PASSWORD, $username, $code)) {
            return ajax_error(ResultCode::ACTION_FAILED, $CodeLogic->getError());
        }

        $UserModel = new UserModel();
        $res = $UserModel->modifyPassword($uid, $password);
        if ($res) {
            //消费验证码
            $CodeLogic->consumeCode(CodeLogic::TYPE_RESET_PASSWORD, $username, $code);

            return ajax_return(ResultCode::ACTION_SUCCESS, '成功重置密码', null);
        } else {
            return ajax_error(ResultCode::ACTION_FAILED, '密码重置失败');
        }
        

    }
}