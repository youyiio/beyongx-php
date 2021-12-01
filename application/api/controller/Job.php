<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\JobModel;
use app\common\model\UserModel;
use think\Validate;

class Job extends Base
{
    //查询岗位字典
    public function dict()
    {
        
        $JobModel = new JobModel();
        $list = $JobModel->field('id,name,title,remark')->select();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
    }
    
    //查询岗位列表
    public function list()
    {
        $params = $this->request->put();

        $page = $params['page']?? 1;
        $size = $params['size']?? 10;
        $filters = $params['filters']?? '';

        $where = [];
        if (isset($filters['title'])) {
            $where[] = ['name', 'like', '%'. $filters['name'] .'%'];
        }
        if (isset($filters['title'])) {
            $where[] = ['title', 'like', '%' . $filters['title'] . '%'];
        }

        $JobModel = new JobModel();
        $fields = 'id,name,title,sort,remark,create_by,create_time,update_by,update_time';
        $list = $JobModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page]);

        $returnData = list_to_hump($list);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增岗位
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'pid' => 'integer',
            'name' => 'alphaDash',
            'title' => 'chsAlphaNum',
            'remark' => 'chsDash',
            'sort' => 'integer'
        ]);

        if (!$validate->check($params)) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '参数错误', $validate->getError());
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);

        $job = new JobModel();
        $params['status'] = JobModel::STATUS_ONLINE;
        $params['create_time'] = date_time();
        $params['update_time'] = date_time();
        $params['create_by'] = $userInfo['nickname']?? '';
        $params['update_by'] = $userInfo['nickname']?? '';
        $job->isUpdate(false)->allowField(true)->save($params);

        $id = $job->id;
        if (!$id) {
            return ajax_return(ResultCode::E_DB_ERROR, '新增失败!');
        }

        $data = JobModel::get($id);
        $returnData = parse_fields($data, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑岗位
    public function edit()
    {
        $params = $this->request->put();
        $id = $params['id'];
        $job = JobModel::get($id);
        if (!$job) {
            return ajax_return(ResultCode::E_DATA_NOT_FOUND, '岗位不存在!');
        }

        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);
        $params['update_by'] = $userInfo['nickname'] ?? '';
        $params['update_time'] = date_time(); 
        $JobModel = new JobModel();
     
        $result = $JobModel->update($params);

        if (!$result) {
            return ajax_return(ResultCode::E_DB_ERROR, '编辑失败!');
        }
        
        $returnData = parse_fields($job, 1);
        
        return ajax_return(ResultCode::E_DB_ERROR, '操作成功!', $returnData);
    }

    //删除岗位
    public function delete($id)
    {
        $dept = JobModel::get($id);
        if (!$dept) {
            $this->error('分类不存在!');
        }
        $dept->delete();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}