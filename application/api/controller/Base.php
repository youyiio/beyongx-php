<?php

namespace app\api\controller;

use think\Controller;
use app\common\library\ResultCode;

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
        $this->user_info = session("jwt_payload_data");
    }

    //空操作：系统在找不到指定的操作方法的时候，会定位到空操作
    public function _empty()
    {
        return ajax_error(ResultCode::SC_FORBIDDEN, '服务器拒绝请求或非法访问！');
    }

    /**
     * 测试生成sign
     * @return string
     */
    // public function testMD5()
    // {
    //     $params = $this->request->put();
    //     dump($params);
    //     $signStr = arrToQuery($params, false);
    //     $sign = strtoupper(md5($signStr . '&key=' . config('appkey')));
    //     return $sign;
    // }

}
