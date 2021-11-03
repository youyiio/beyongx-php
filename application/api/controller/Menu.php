<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\AuthRuleModel;

class Menu extends Base
{
    public function list()
    {
        $params = $this->request->put();
        $page = $params['page'];
        $size = $params['size'];

        $filters = $params['filters'];
        $keyword = $filters['keyword']?? '';

        $where = [];
        $where[] = ['belongs_to', '=', 'admin'];
        if (!empty($keyword)) {
            $where[] = ['title', 'like', '%'.$keyword.'%'];
        }
        
        $AuthRuleModel = new AuthRuleModel();
        $list = $AuthRuleModel->where($where)->order('id asc')->paginate($size, false, ['page'=>$page]);
       
        $list = $list->toArray();
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];

        // 获取树形或者结构数据
        $tree = new \beyong\commons\data\Tree();
        $data = $tree::tree($list['data'], 'title', 'id', 'pid');
        
        //返回数据
        $returnData['records'] = parse_fields($data, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }
}