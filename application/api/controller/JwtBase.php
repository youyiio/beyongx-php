<?php

namespace app\api\controller;

use think\facade\Request;

class JwtBase extends Base {
    // 用户信息
    protected $user_info;

    public function initialize() {
        $token = Request::header('authorization');

        // 验证是否登录
        if (is_null($token)) {
            header('Content-Type:application/json; charset=utf-8');
            $response = json_encode([
                'code'  => ERRNO['SESSIONERR'],
                'message' => '用户未登陆'
            ]);
            exit($response);
        }

        // 验证登录是否过期
        $user_info = \getJWT($token);
        if (is_null($user_info)) {
            header('Content-Type:application/json; charset=utf-8');

            $response = json_encode([
                'code'  => ERRNO['SESSIONERR'],
                'message' => '登录已过期'
            ]);
            exit($response);
        }

        // 存储用户信息
        $this->user_info = $user_info;
    }
}