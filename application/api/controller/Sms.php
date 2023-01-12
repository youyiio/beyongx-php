<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\logic\UserLogic;
use app\common\model\UserModel;
use think\facade\Cache;
use beyong\commons\utils\PregUtils;
use beyong\commons\utils\StringUtils;

use Firebase\JWT\JWT;
use app\common\logic\CodeLogic;
use app\common\model\UserRoleModel;

class Sms extends Base
{
    public function sendCode() 
    {
        if ($this->request->method() != 'POST') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        $params = $this->request->put();
        $mobile = $params["mobile"];
        $action = isset($params["action"]) ? $params["action"] : "login";
        if (!PregUtils::isMobile($mobile)) {
            return ajax_error(ResultCode::ACTION_FAILED, "手机号格式不正确!");
        }

        if (!in_array($action, [CodeLogic::TYPE_REGISTER, CodeLogic::TYPE_LOGIN, CodeLogic::TYPE_RESET_PASSWORD])) {
            return ajax_error(ResultCode::E_PARAM_VALIDATE_ERROR, '短信action类型不正确!');
        }

        // 防止短信被刷
        $mobileFrequency = Cache::get($mobile . "_send_code_frequency");
        $ipFrequency = Cache::get($this->request->ip(0, true) . "_send_code_frequency");
        $frequencyLimited = !empty($mobileFrequency) || !empty($ipFrequency);
        if ($frequencyLimited) {
            return ajax_error(ResultCode::ACTION_FAILED, "您操作过于频繁，请稍候再试!");
        }

        //验证码发送
        try {
            $codeLogic = new CodeLogic();
                 
            $codeLogic->sendCodeByMobile($mobile, $action);
        } catch(\Exception $e) {
            return ajax_error(ResultCode::ACTION_FAILED, $e->getMessage());
        }

        //频率限制
        Cache::set($mobile . "_send_code_frequency", '60s', 60);
        Cache::set($this->request->ip(0, true) . "_send_code_frequency", '20s', 20);

        return ajax_success(null);        
    }

    public function login()
    {
        if ($this->request->method() != 'POST') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        $params = $this->request->put();

        $mobile = $params["mobile"];
        $code = $params["code"];
        if (!PregUtils::isMobile($mobile)) {
            return ajax_error(ResultCode::ACTION_FAILED, "手机号格式不正确!");
        }
        if (strlen($code) !== 6 || !PregUtils::isNumber($code)) {
            return ajax_error(ResultCode::ACTION_FAILED, "验证码为6位的数字!");
        }

        //登录次数判断
        $tryLoginCountMark = $mobile . '_try_login_count';
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
        Cache::inc($tryLoginCountMark);

        $codeLogic = new CodeLogic();
        $check = $codeLogic->checkCode(CodeLogic::TYPE_LOGIN, $mobile, $code);
        if ($check !== true) {
            return ajax_error(ResultCode::E_PARAM_ERROR, $codeLogic->getError());
        }

        //清掉验证码
        $codeLogic->consumeCode(CodeLogic::TYPE_LOGIN, $mobile, $code);

        Cache::rm($tryLoginCountMark);

        $UserMobile = new UserModel();
        $user = $UserMobile->findByMobile($mobile);
        if (!$user) {
            $user = $this->addUser($mobile);
            if (!$user) {
                return ajax_error(ResultCode::E_PARAM_ERROR, "添加用户失败！");
            }
        }

        $payload = [
            'iss' => 'jwt_admin',  //该JWT的签发者
            'aud' => 'jwt_api', //接收该JWT的一方，可选
            'iat' => time(),  //签发时间
            'exp' => time() + config('jwt.jwt_expired_time'),  //过期时间
//            'nbf' => time() + 60,  //该时间之前不接收处理该Token
            'sub' => 'domain',  //面向的用户
            'jti' => md5(uniqid('JWT') . time()),  //该Token唯一标识
            'data' => [
                'uid' => $user['id'],
            ]
        ];
        $token = JWT::encode($payload, config('jwt.jwt_key'), config('jwt.jwt_alg'));

        $data = [
            'token' => 'Bearer ' . $token
        ];

        return ajax_return(ResultCode::ACTION_SUCCESS, "登录成功!", $data);
    }

    // 短信登录时，若用户不存在是，添加用户
    private function addUser($mobile)
    {
        $nickname = '用户' . substr($mobile, 5);
        $password = StringUtils::getRandString(12);

        $UserLogic = new UserLogic();
        $user = $UserLogic->register($mobile, $password, $nickname, '', '', UserModel::STATUS_ACTIVED);
        if (!$user) {
            return $user;
        }

        $UserModel = new UserModel();
        //完善用户资料
        $profileData = [
            'id' => $user['id'],
            'head_url' => '/static/cms/image/head/0002.jpg',
            'referee' => 1, //$data['referee'], //推荐人
            'register_ip' => request()->ip(0, true),
            'from_referee' => sub_str(cookie('from_referee'), 0, 250),
            'entrance_url' => sub_str(cookie('entrance_url'), 0, 250),
        ];
        $UserModel->where('id', $user['id'])->setField($profileData);

        //权限初始化
        $group[] = [
            'uid' => $user->id,
            'role_id' => config('user_group_id')
        ];
        $UserRoleModel = new UserRoleModel();
        $UserRoleModel->insertAll($group);

        return $user;
    }
}