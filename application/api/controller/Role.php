<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\AuthGroupModel;

class Role extends Base
{
    public function list()
    {
        $params = $this->request->put();
        $page = $params['page'];
        $size = $params['size'];

        $filters = $params['filters'];
        $keyword = $filters['keyword'];

        $where = [];
        $fields = 'id,title,status';
        if (!empty($keyword)) {
            $where[] = ['title', 'like', '%'.$keyword.'%'];
        }

        $AuthGroupModel = new AuthGroupModel();
        $list = $AuthGroupModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page]);

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