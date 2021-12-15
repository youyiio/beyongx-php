<?php

namespace app\api\controller;

use think\Controller;
use app\common\library\ResultCode;
use Firebase\JWT\JWT;
use think\facade\Log;

use app\common\exception\JwtException;
use app\api\library\RolePermission;
use think\facade\Request;

class Base extends Controller
{

    // 用户信息，来自playload data
    protected $user_info;

    //路由miss
    public function miss() {
        return json([
            'code' => ResultCode::E_PARAM_ERROR,
            'message'   => '访问接口不存在或参数错误']);
    }

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return ajax_error(ResultCode::SC_FORBIDDEN, '服务器拒绝请求或非法访问！');
    }

    public function initialize() 
    {
        $url = strtolower(Request::url());
        $url = str_replace("/api", "", $url);
        if (in_array($url, config('jwt.jwt_action_excludes'))) {
            return true;
        }
        
        //jwt验签及解码，用户角色权限验证
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
            throw new JwtException(ResultCode::E_TOKEN_INVALID, 'TOKEN不合法！' . $e->getMessage(), 'E_TOKEN_INVALID');
        }
        
        if (is_null($payload) || is_null($payload->data)) {
            throw new JwtException(ResultCode::E_TOKEN_EXPIRED, '登录已过期！', 'E_TOKEN_EXPIRED');
        }

        $this->user_info = $payload->data;

        //Api权限验证
        if (config('jwt.jwt_auth_on') !== 'off') {
            $uid = $this->user_info->uid;   
            $permission = request()->controller() . ':' . request()->action();
            $permission = strtolower($permission);
            $rolePermission = new RolePermission();        
            if (!$rolePermission->checkPermission($uid, $permission)) {
                throw new JwtException(ResultCode::E_ACCESS_NOT_AUTH, "访问的资源没有权限：Subject has no permission [$permission]", 'E_ACCESS_NOT_AUTH');
            }
        }

        // $authorization = $this->request->header('authorization');
        // if (!empty($authorization)) {
        //     $token = substr($authorization, 7);
        //     $payload = null;
        //     try {
        //         $payload = JWT::decode($token, config('jwt.jwt_key'), [config('jwt.jwt_alg')]);
        //         $this->user_info = $payload->data;
        //     } catch (\Throwable $e) {
        //         Log::error("jwt decode error:" . $e->getMessage());
        //     }
        // }
    }

}
