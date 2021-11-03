<?php

namespace app\api\behavior;

use Firebase\JWT\JWT;
use think\exception\ValidateException;
use app\common\exception\JwtException;
use think\facade\Request;
use app\common\library\ResultCode;

class JWTBehavior
{

    public function run()
    {
        $url = strtolower(Request::url());
        $url = str_replace("/api", "", $url);
        if (in_array($url, config('jwt.jwt_action_excludes'))) {
            return true;
        }
        
        $authorization = Request::header('authorization');

        if (!$authorization) {
            throw new JwtException(ResultCode::E_TOKEN_EMPTY, 'TOKEN参数缺失！', 'E_TOKEN_EMPTY');
        }

        $type = substr($authorization, 0, 6);
        if ($type !== 'Bearer') {
            throw new JwtException(ResultCode::E_TOKEN_INVALID, 'TOKEN类型错误！', 'E_TOKEN_INVALID');
        }

        $token = substr($authorization, 7);
        $payload = null;
        try {
            $payload = JWT::decode($token, config('jwt.jwt_key'), [config('jwt.jwt_alg')]);
        } catch (\Throwable $e) {}
        
        if (is_null($payload) || is_null($payload->data)) {
            throw new JwtException(ResultCode::E_TOKEN_EXPIRED, '登录已过期！', 'E_TOKEN_EXPIRED');
        }

        //Api权限验证
        if (config('jwt.jwt_auth_on') !== 'off') {
            $user_info = $payload->data;
            $uid = $user_info->uid;      
            $node = request()->module().'/'.request()->controller().'/'.request()->action();
            $auth = \think\auth\Auth::instance();        
            if (!$auth->check($node, $uid)) {
                throw new ValidateException(ResultCode::E_ACCESS_NOT_AUTH, "$node 没有访问权限");
            }
        }

        session('jwt_payload_data', $payload->data);

        return true;
    }
}