<?php
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\cms\LinkModel;
use think\Validate;

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
        
        $list = $LinkModel->where($where)->field($fields)->paginate($size, false, ['page' =>$page])->toArray();

        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //新增链接
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'title' => 'require',
            'url' => 'require|url',
            'sort' => 'integer',
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误!');
        }
        if (empty($params['startTime'])) {
            $params['startTime'] = null;
        }
        if (empty($params['endTime'])) {
            $params['startTime'] = null;
        }
        
        $LinkModel = new LinkModel();
        $res = $LinkModel->allowField(true)->save($params);
        $id = $LinkModel->id;
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        } 
        cache('links',null);

        $returnData = LinkModel::get($id);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //编辑链接
    public function edit()
    {
        $params = $this->request->put();

        $link = LinkModel::get($params['id']);
        if (!$link) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误!');
        }

        $validate = Validate::make([
            'title' => 'require',
            'url' => 'require|url',
            'sort' => 'integer',
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误!');
        }
        if (empty($params['startTime'])) {
            $params['startTime'] = null;
        }
        if (empty($params['endTime'])) {
            $params['startTime'] = null;
        }

        $res = $link->isUpdate(true)->save($params);

        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $returnData = parse_fields($link, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //删除链接
    public function delete($id)
    {
        $link = LinkModel::get($id);
        $res = $link->delete();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}