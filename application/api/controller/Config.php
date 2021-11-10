<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\ConfigModel;
use think\Validate;

class Config extends Base
{
    //查询字典信息
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

    //查询字典信息
    public function query()
    {
        $params = $this->request->put();
        $key = $params['key']??'';
        $group = $params['group']??'';

        if (empty($key)) {
            return ajax_return(ResultCode::E_PARAM_EMPTY, '参数为空');
        }
        if (!empty($group)) {
            $where[] = ['group', '=', $group];
        }

        $where[] = ['key', '=', $key];
       
        $ConfigModel = new ConfigModel();
        $list = $ConfigModel->where($where)->find();

        $returnData = parse_fields($list, 1);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //新增字典
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
            return ajax_return(ResultCode::E_ACCESS_NOT_FOUND, '数据未找到!');
        }

        $res = $config->isUpdate(false)->allowField(true)->save($params, ['id'=>$id]);

        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $returnData = parse_fields($config, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //查询状态字典
    public function status($name)
    {
        $ConfigModel = new ConfigModel();
        $list = $ConfigModel->where('group', '=', $name.'_status')->field('key,value')->select();

        if (empty($list)) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '数据未找到');
        }

        $returnData = [];
        foreach ($list as $val) {
            $returnData[] = [
                $val['key'] => $val['value']
            ];
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功', $returnData);
    }

    //删除字典
    public function delete($id)
    {
        //删除role表中的数据
        $config = ConfigModel::get($id);
        $res = $config->delete();
        
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}