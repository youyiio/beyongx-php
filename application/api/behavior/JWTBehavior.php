<?php

namespace app\api\behavior;

use Firebase\JWT\JWT;
use think\exception\ValidateException;
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
            throw new ValidateException('TOKEN参数缺失！');
        }

        $type = substr($authorization, 0, 6);
        if ($type !== 'Bearer') {
            throw new ValidateException('TOKEN类型错误！');
        }

        $token = substr($authorization, 7);
        $payload = JWT::decode($token, config('jwt.jwt_key'), [config('jwt.jwt_alg')]);

        //Api权限验证
        $user_info = $payload->data;
        $uid = $user_info['uid'];      
        $node = request()->module().'/'.request()->controller().'/'.request()->action();
        $auth = \think\auth\Auth::instance();        
        if (!$auth->check($node, $uid)) {
            throw new ValidateException(ResultCode::ACCESS_NOT_AUTH, "$node 没有访问权限");
        }

        session('jwt_payload_data', $payload->data);

        return true;
    }
}