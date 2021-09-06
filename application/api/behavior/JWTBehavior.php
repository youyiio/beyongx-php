<?php

namespace app\api\behavior;

use Firebase\JWT\JWT;
use think\exception\ValidateException;
use think\facade\Request;

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
            throw new ValidateException('TOKEN参数缺失！');
        }

        $type = substr($authorization, 0, 6);
        if ($type !== 'Bearer') {
            throw new ValidateException('TOKEN类型错误！');
        }

        $token = substr($authorization, 7);
        $payload = JWT::decode($token, config('jwt.jwt_key'), [config('jwt.jwt_alg')]);

        session('jwt_payload_data', $payload->data);

        return true;
    }
}