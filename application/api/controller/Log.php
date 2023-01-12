<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\ActionLogModel;
use app\common\model\UserModel;

class Log extends Base 
{
    //日志列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page'];
        $size = $params['size'];

        $filters = $params['filters'];
        $action = $filters['action'] ?? '';
        $startTime = $filters['startTime'] ?? '';
        $endTime = $filters['endTime'] ?? '';
        $username = $filters['username'] ?? '';
       
        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d',strtotime('-31 day'));
            $endTime = date('Y-m-d');
        }

        $startDatetime = date('Y-m-d 00:00:00', strtotime($startTime));
        $endDatetime = date('Y-m-d 23:59:59', strtotime($endTime));
        $where[] = ['create_time', 'between', [$startDatetime, $endDatetime]];

        if ($action !== '') {
            $where[] =  ['action', '=', $action];
        }
        if ($username !== '') {
            $where[] = ['username', '=', $username];
        }

        $fields = 'id,username,action,module,component,ip,action_time,response_time,params,user_agent,remark,create_time';
        $ActionLogModel = new ActionLogModel();
        $list = $ActionLogModel->where($where)->field($fields)->order('id desc')->paginate($size, false, ['page'=>$page]);
        
        //处理数据
        foreach ($list as $val) {
            $user = UserModel::get($val['username']);
            $val['username'] = $user['nickname'];
            $val['address'] = ip_to_address($val['ip'], 'province,city');
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