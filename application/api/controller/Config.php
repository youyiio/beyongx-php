<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\ConfigModel;
use think\Validate;

class Config extends Base
{
    public function list()
    {
        $params = $this->request->put();
        $page = $params['page']?? '1';
        $size = $params['size']?? '10';
        $filters = $params['filters'];

        $where = [];
        if (!empty($filters['keyword'])) {
            $where[] = ['name', 'like', '%'.$filters['keyword'].'%'];
        }
        if (!empty($filters['group'])) {
            $where[] = ['group', '=', $filters['group']];
        }
        if (!empty($filters['key'])) {
            $where[] = ['key', 'like', '%'.$filters['key'].'%'];
        }

        $ConfigModel = new ConfigModel();
        $list = $ConfigModel->where($where)->paginate($size, false, ['page'=>$page])->toArray();

        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增字段
    public function create()
    {
        $params = $this->request->put();
        $validate = Validate::make([
            'name' => 'require',
            'group' => 'require',
            'key' => 'require',
            'value' => 'require',
            'value_type' => 'require',
        ]);

        if (!$validate->check($params)) {
            ajax_return(ResultCode::E_PARAM_ERROR, '操作成功!');
        }

        $Config = new ConfigModel();
        $Config->name = $params['name'];
        $Config->group = $params['group']; 
        $Config->key = $params['key']; 
        $Config->value = $params['value']; 
        $Config->value_type = $params['value_type']; 
        $Config->sort = $params['sort']?? ''; 
        $Config->remark = $params['remark']?? ''; 
        $Config->save();

        $id = $Config->id;

        if (!$id) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $list = ConfigModel::get($id);

        $returnData = parse_fields($list->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //编辑字典
    public function edit()
    {
        $params = $this->request->put();
        $id = $params['id'];

        $config = ConfigModel::get($id);

        if (!$config) {
            return ajax_return(ResultCode::E_ACCESS_NOT_FOUND, '操作失败!');
        }


    }
}