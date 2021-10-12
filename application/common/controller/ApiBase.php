<?php
namespace app\api\controller;

use app\common\library\ResultCode;
use think\facade\Request;

/**
 * Trait api接口 Base Controller 组件
 * 使用方法：use \app\common\controller\ApiBase;
 * @package app\common\controller
 */
trait JwtBase
{
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

        //Api权限验证        
        $uid = $user_info['uid'];
        $node = request()->module().'/'.request()->controller().'/'.request()->action();
        $auth = \think\auth\Auth::instance();
        if (!$auth->check($node, $uid)) {
            $response = json_encode([
                'code'  => ResultCode::ACCESS_NOT_AUTH,
                'message' => "$node 没有访问权限"
            ]);
            exit($response);
        }
    }
}