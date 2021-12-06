<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\DeptModel;
use app\common\model\UserModel;
use think\Validate;

class Dept extends Base
{
    //查询部门字典
    public function dict()
    {
        $params = $this->request->put();

        $struct = $params['struct']?? '';

        $DeptModel = new DeptModel();
        $list = $DeptModel->field('id,name,title')->select();

        if ($struct === 'list') {
            $data = getList($list);
        } else {
            $data = getTree($list, 0, 'id', 'pid', 3);
        }

        $returnData = parse_fields($data, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //查询部门列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page']?? 1;
        $size = $params['size']?? 10;
        $filters = $params['filters']?? '';
        $pid = $filters['pid']?? 0;
        $depth = $filters['depth']?? 1;
        $struct = $filters['struct']?? '';

        $DeptModel = new DeptModel();
        $list = $DeptModel->select()->toArray();

        // 获取树形或者list数据
        if ($struct === 'list') {
            $data = getList($list, $pid, 'id', 'pid');
        } else {
            $data = getTree($list, $pid, 'id', 'pid', $depth);
        }
        
        //分页
        $total = count($data);  //总数
        $pages = ceil($total / $size); //总页数
        $start = ($page - 1) * $size;
        $records =  array_slice($data, $start, $size); 
    
        //返回数据
        $returnData['current'] = $page;
        $returnData['pages'] = $pages;
        $returnData['size'] = $size;
        $returnData['total'] = $total;
        $returnData['records'] = parse_fields($records, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增部门
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'pid' => 'integer',
            'name' => 'require|chsDash',
            'remark' => 'chsDash',
            'sort' => 'integer'
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误', $validate->getError());
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);

        $dept = new DeptModel();
        $params['create_time'] = date_time();
        $params['update_time'] = date_time();
        $params['create_by'] = $userInfo['nickname']?? '';
        $params['update_by'] = $userInfo['nickname']?? '';
        $dept->isUpdate(false)->allowField(true)->save($params);

        $id = $dept->id;
        if (!$id) {
            return ajax_return(ResultCode::E_DB_ERROR, '新增失败!');
        }

        $data = DeptModel::get($id);
        $returnData = parse_fields($data->toArray(), 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑部门
    public function edit()
    {
        $params = $this->request->put();
        $id = $params['id'];
        $dept = DeptModel::get($id);
        if (!$dept) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '部门不存在!');
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);
        $params['update_by'] = $userInfo['nickname'] ?? '';
        $params['update_time'] = date_time(); 
        $DeptModel = new DeptModel();
     
        $result = $DeptModel->update($params);

        if (!$result) {
            return ajax_return(ResultCode::E_DB_ERROR, '编辑失败!');
        }
        
        $returnData = parse_fields($dept->toArray(), 1);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //删除部门
    public function delete($id)
    {
        $dept = DeptModel::get($id);
        if (!$dept) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '部门不存在!');
        }
        $dept->delete();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}