<?php

use think\facade\Log;

use Firebase\JWT\JWT;
use app\common\library\ResultCode;
use app\common\model\cms\ArticleMetaModel;
use app\common\model\FileModel;
use app\common\model\ImageModel;

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

// 转为驼峰后的pagelist
function list_to_hump($list)
{

    $list = $list->toArray();

    $return['current'] = $list['current_page'];
    $return['pages'] = $list['last_page'];
    $return['size'] = $list['per_page'];
    $return['total'] = $list['total'];
    $return['records'] = parse_fields($list['data'], 1);

    return $return;
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
function getList($data, $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
{
    if (empty($data)) {
        return array();
    }
    $arr = array();
    foreach ($data as $v) {
        $id = $v[$fieldPri];
        if ($v[$fieldPid] == $pid) {
            $v['level'] = $level;
           
            array_push($arr, $v);
            $tmp = getList($data, $id, $fieldPri, $fieldPid, $level + 1);
            $arr = array_merge($arr, $tmp);
        }
    }
    return array_merge($arr);
}

//查找文章的缩略图
function findThumbImage($art)
{
    $thumbImage = [];
    if (empty($art['thumb_image_id']) || $art['thumb_image_id'] == 0) {
        return $thumbImage;
    }

    $ImageModel = new ImageModel();
    $fields= 'id,name,thumb_image_url,create_time,oss_url,file_url';
    $thumbImage = $ImageModel->where('id', '=', $art['thumb_image_id'])->field($fields)->find();
    unset($art['thumb_image_id']);
    if (empty($thumbImage)) {
        return $thumbImage;
    }

    //返回数据
    $data['id'] = $thumbImage['id'];
    $data['name'] = $thumbImage['name'];
    $data['thumbImageUrl'] = $thumbImage['thumb_image_url'];
    $data['FullThumbImageUrlAttr'] = $ImageModel->getFullThumbImageUrlAttr('', $thumbImage);
    $data['ImageUrl'] = $thumbImage['file_url'];
    $data['fullImageUrl'] = $ImageModel->getFullImageUrlAttr('', $thumbImage);
    $data['ossImageUrl'] = $thumbImage['oss_url'];
    $data['createTime'] = $thumbImage['create_time'];

    return $data;
}

//查找文章的附加图片
function FindMetaImages($art)
{
    $where = [
        ['article_id', '=', $art['id']],
        ['meta_Key', '=', 'image']
    ];

    $metaImageIds = ArticleMetaModel::where($where)->column('meta_value');

    $ImageModel = new ImageModel();
    $fields = 'id,name,thumb_image_url,create_time,oss_url,file_url';
    $metaImages = $ImageModel->where('id', 'in', $metaImageIds)->field($fields)->select();

    $data = [];
    foreach ($metaImages as $key => $image) {
        //获取完整路径
        $data[$key]['id'] = $image['id'];
        $data[$key]['name'] = $image['name'];
        $data[$key]['thumbImageUrl'] = $image['thumb_image_url'];
        $data[$key]['FullThumbImageUrlAttr'] = $ImageModel->getFullThumbImageUrlAttr('', $image);
        $data[$key]['ImageUrl'] = $image['file_url'];
        $data[$key]['fullImageUrl'] = $ImageModel->getFullImageUrlAttr('', $image);
        $data[$key]['ossImageUrl'] = $image['oss_url'];
        $data[$key]['createTime'] = $image['create_time'];
    }

    return $data;
}

//查找文章的附加文件
function findMetaFiles($art)
{
    $where = [
        ['article_id', '=', $art['id']],
        ['meta_Key', '=', 'file']
    ];

    $metaFileIds = ArticleMetaModel::where($where)->column('meta_value');

    $FileModel = new FileModel();
    $fields = 'id,name,file_url,file_path,size,create_time';
    $metafiles = $FileModel->where('id', 'in', $metaFileIds)->field($fields)->select();

    $data = [];
    foreach ($metafiles as $key => $file) {
        //获取完整路径
        $data[$key]['id'] = $file['id'];
        $data[$key]['name'] = $file['name'];
        $data[$key]['fileUrl'] = $file['file_url'];
        $data[$key]['fullFileUrl'] = $file->getFullFileUrlAttr('', $file);
        $data[$key]['filePath'] = $file['file_path'];
        $data[$key]['size'] = $file['size'];
        $data[$key]['createTime'] = $file['create_time'];
    }

    return $data;
}