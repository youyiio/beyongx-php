<?php

namespace app\api\controller;

use think\Controller;
use app\common\library\ResultCode;
use Firebase\JWT\JWT;
use think\facade\Log;
class Base extends Controller
{

    // 用户信息，来自playload data
    protected $user_info;

    public function miss() {
        return json([
            'code' => ResultCode::E_PARAM_ERROR,
            'message'   => '访问接口不存在或参数错误']);
    }

    public function initialize() {
        $authorization = $this->request->header('authorization');
        if (!empty($authorization)) {
            $token = substr($authorization, 7);
            $payload = null;
            try {
                $payload = JWT::decode($token, config('jwt.jwt_key'), [config('jwt.jwt_alg')]);
                $this->user_info = $payload->data;
            } catch (\Throwable $e) {
                Log::error("jwt decode error:" . $e->getMessage());
            }
        }
    }

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return ajax_error(ResultCode::SC_FORBIDDEN, '服务器拒绝请求或非法访问！');
    }

}
