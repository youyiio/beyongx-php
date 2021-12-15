<?php

namespace app\api\behavior;

use Firebase\JWT\JWT;
use think\exception\ValidateException;
use app\common\exception\JwtException;
use think\facade\Request;
use app\common\library\ResultCode;
use think\facade\Log;
use app\api\library\RolePermission;

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
        } catch (\Throwable $e) {
            Log::error("jwt decode error:" . $e->getMessage());
            throw new JwtException(ResultCode::E_TOKEN_INVALID, 'TOKEN不合法！', 'E_TOKEN_INVALID');
        }
        
        if (is_null($payload) || is_null($payload->data)) {
            throw new JwtException(ResultCode::E_TOKEN_EXPIRED, '登录已过期！', 'E_TOKEN_EXPIRED');
        }

        //Api权限验证
        if (config('jwt.jwt_auth_on') !== 'off') {
            $user_info = $payload->data;
            $uid = $user_info->uid;   
            $permission = request()->controller() . ':' . request()->action();
            $permission = strtolower($permission);
            $rolePermission = new RolePermission();        
            if (!$rolePermission->checkPermission($uid, $permission)) {
                throw new JwtException(ResultCode::E_ACCESS_NOT_AUTH, "访问的资源没有权限：Subject has no permission [$permission]", 'E_ACCESS_NOT_AUTH');
            }
        }

        return true;
    }
}