<?php
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\cms\LinkModel;

class Link extends Base
{
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page'];
        $size = $params['size'];
        $filters = $params['filters'] ?? []; 

        $where = [];
        $fields = 'id,title,url,sort,status,start_time,end_time,create_time';
        if (isset($filters['keyword'])) {
            $where[] = ['title', 'like', '%'.$filters['keyword'].'%'];
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = ['status', '=', $filters['status']];
        }

        $LinkModel = new LinkModel();
        
        $list = $LinkModel->where($where)->field($fields)->paginate($size, false, ['page' =>$page]);

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