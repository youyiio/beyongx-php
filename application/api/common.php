<?php

use think\facade\Log;

use Firebase\JWT\JWT;
use app\common\library\ResultCode;

const ERRNO_MAP = [
    'OK'         => '成功',
    'DBERR'      => '数据库查询错误',
    'NODATA'     => '无数据',
    'DATAEXIST'  => '数据已存在',
    'DATAERR'    => '数据错误',
    'SESSIONERR' => '用户未登录',
    'LOGINERR'   => '用户登录失败',
    'PARAMERR'   => '参数错误',
    'USERERR'    => '用户不存在或未激活',
    'ROLEERR'    => '用户身份错误',
    'PWDERR'     => '密码错误',
    'REQERR'     => '非法请求或请求次数受限',
    'IPERR'      => 'IP受限',
    'THIRDERR'   => '第三方系统错误',
    'IOERR'      => '文件读写错误',
    'SERVERERR'  => '内部错误',
    'UNKOWNERR'  => '未知错误',
];
const ERRNO = [
    'OK'         => '1',
    'DBERR'      => '4001',
    'NODATA'     => '4002',
    'DATAEXIST'  => '4003',
    'DATAERR'    => '4004',
    'SESSIONERR' => '4101',
    'LOGINERR'   => '4102',
    'PARAMERR'   => '4103',
    'USERERR'    => '4104',
    'ROLEERR'    => '4105',
    'PWDERR'     => '4106',
    'REQERR'     => '4201',
    'IPERR'      => '4202',
    'THIRDERR'   => '4301',
    'IOERR'      => '4302',
    'SERVERERR'  => '4500',
    'UNKOWNERR'  => '4501',
];

class ApiCode {
    const E_USER_LOGIN_ERROR			 = 10009;		//登录方式不正确
}

// 向前端返回JSON数据
function ajax_return($code, $message = 'success', $data = []) {

    return json([
        'code' => $code,
        'message' => $message,
        'data'  => $data
    ]);
    
}
// 向前端返回JSON SUCCESS数据
function ajax_success($data = []) {

    return json([
        'code' => ResultCode::ACTION_SUCCESS,
        'message' => 'success',
        'data'  => $data
    ]);
    
}
// 向前端返回JSON ERROR数据
function ajax_error($code, $message = 'fail') {
    return json([
        'code' => $code,
        'message' => $message,
        'data'  => null
    ]);
}

// 格式标准的page
function to_standard_pagelist($paginator)
{

    return [
        'total'    => $paginator->total(),
        'size'     => $paginator->listRows(),
        'current'  => $paginator->currentPage(),
        'records'  => $paginator->items(),
    ];
}

// 设置JWT
function setJWT($data) {
    $jwt = new JWT();

    $token = array(
        // "iss"  => "http://example.org", // 签发者
        // "aud"  => "http://example.com", // 认证者
        'iat'  => time(), // 签发时间
        'nbf'  => time(), // 生效时间
        'exp'  => (time() + 60 * 60 * 24 * 7), // 过期时间  7天后的时间戳
        'data' => $data,
    );
    $jwt = $jwt::encode($token, config('jwt_jwt_key'), config('jwt_jwt_alg'));

    return $jwt;
}

// 获取JWT内容
function getJWT($token) {
    $jwt = new JWT();
    $data = null;
    try {
        $jwt_data = $jwt::decode($token, config('jwt.jwt_key'), config('jwt.jwt_alg'));
        $data     = (array) ($jwt_data->data);
    } catch (\Throwable $e) {
        Log::write($e->getMessage(), 'error');
        return null;
    }

    return $data;
}