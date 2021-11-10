<?php

use think\facade\Log;

use Firebase\JWT\JWT;
use app\common\library\ResultCode;


class ApiCode {
    const E_USER_LOGIN_ERROR = 10009; //登录方式不正确
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
function ajax_error($code, $message = 'fail', $error = '') {
    return json([
        'code' => $code,
        'message' => $message,
        'data'  => null,
        'error' => $error
    ]);
}

// 格式标准的page
function to_standard_pagelist($paginator)
{

    return [
        'total'    => $paginator->total(),
        'size'     => $paginator->listRows(),
        'pages'     => $paginator->lastPage(),
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
        $data     = $jwt_data->data;
    } catch (\Throwable $e) {
        Log::write($e->getMessage(), 'error');
        return null;
    }

    return $data;
}

//获取树状结构
function getTree($data, $pid = 0, $fieldPK = 'id', $fieldPid = 'pid', $depth = 1, $currentDepth = 1)
{
    if (empty($data)) {
        return array();
    }
    
    $arr = array();
    foreach ($data as $v) {
        if ($v[$fieldPid] == $pid) {
            $arr[$v[$fieldPK]] = $v;
            $arr[$v[$fieldPK]]['level'] = $currentDepth;
            $children = getTree($data, $v[$fieldPK], $fieldPK, $fieldPid, $depth, $currentDepth + 1);

            //判断是否有children
            if (empty($children)) {
                $arr[$v[$fieldPK]]["hasChildren"] = false;
                continue;
            }
            $arr[$v[$fieldPK]]["hasChildren"] = true;

            //判断深度
            if ($depth == $currentDepth) {
                $arr[$v[$fieldPK]]['children'] = [];
                continue;
            }
            $arr[$v[$fieldPK]]['children'] = $children;
        }
    }

    return array_merge($arr);
}

//获取list数据结构
function getList($data, $pid = 0, $fieldPri = 'cid', $fieldPid = 'pid', $level = 1)
{
    if (empty($data)) {
        return array();
    }
    $arr = array();
    foreach ($data as $v) {
        $id = $v[$fieldPri];
        if ($v[$fieldPid] == $pid) {
            $v['_level'] = $level;
           
            array_push($arr, $v);
            $tmp = getList($data, $id, $fieldPri, $fieldPid, $level + 1);
            $arr = array_merge($arr, $tmp);
        }
    }
    return array_merge($arr);
}