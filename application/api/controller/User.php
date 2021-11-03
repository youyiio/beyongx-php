<?php

namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\UserModel;

class User extends Base
{

    // 获取用户信息
    public function getInfo()
    {
        if ($this->request->method() != 'GET') {
            return ajax_error(ResultCode::SC_FORBIDDEN, '非法访问！请检查请求方式！');
        }

        $params = $this->request->put();
        
        $payloadData = session('jwt_payload_data');
        if (!$payloadData) {
            return ajax_error(ResultCode::ACTION_FAILED, 'TOKEN自定义参数不存在！');
        }
        $uid = $payloadData->uid;
        if (!$uid) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }
        $user = UserModel::get(['id' => $uid]);
        if (empty($user)) {
            return ajax_error(ResultCode::E_USER_NOT_EXIST, '用户不存在！');
        }

        $returnData = [
            'uid' => $uid,
            //'account' => $user->account,
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'email' => $user->email,
            //'status' => $user->status,
            'headUrl' => $user->head_url,
            'sex' => $user->sex,
            'registerTime' => $user->register_time,
            'roles' => ['admin']
        ];

        return ajax_success($returnData);
    }

    //获取用户列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page'];
        $size = $params['size'];
        $filters = $params['filters'] ?? []; 

        $where = [];
        $fields = 'id,nickname,sex,mobile,email,head_url,qq,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        if (isset($filters['keyword'])) {
            $where[] = ['id|mobile|email|nickname', 'like', '%'.$filters['keyword'].'%'];
        }

        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page' =>$page]);

        //查询部门
        foreach ($list as $val) {
            $val['dept'] = [];
        }

        $list = $list->toArray();
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }
}
