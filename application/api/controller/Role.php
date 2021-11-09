<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\AuthGroupAccessModel;
use app\common\model\AuthGroupModel;
use app\common\model\AuthRuleModel;
use app\common\model\UserModel;
use think\facade\Cache;

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
        $list = $AuthGroupModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page])->toArray();

        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增角色
    public function create()
    {
        $params = $this->request->put();
        
        $name = $params['name']?? '';
        $remark = $params['remark']?? '';

        if (empty($name)) {
            return ajax_return(ResultCode::E_DATA_VALIDATE_ERROR, '操作失败!');
        }

        $AuthGroupModel = new AuthGroupModel();
        $result = $AuthGroupModel->save(['title'=>$name]);
        // $result = $AuthGroupModel->save([['title'=>$name], ['remark'=>$remark]]);
        if (!$result) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        $id = $AuthGroupModel->id;
        $returnData = $AuthGroupModel->where('id', '=' ,$id)->find();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑角色
    public function edit()
    {
        $params = $this->request->put();

        $id = $params['id'];

        $role = AuthGroupModel::get($id);

        if (!$role) {
            return ajax_return(ResultCode::E_PARAM_ERROR, '角色不存在!');
        }

        $role->title = $params['name'];
        // $role->remark = $params['remark'];
        $res = $role->save();
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $role);
    }

    //删除角色
    public function delete($id)
    {
        
        //删除AuthGroup表中的数据
        $role = AuthGroupModel::get($id);
        $res = $role->delete();
        
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        }

        //删除AuthGroupAccess表中的数据
        $AuthGroupAcessModel = new AuthGroupAccessModel();
        $AuthGroupAcessModel->where('group_id', '=', $id)->delete();

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查询角色权限
    public function menus($id)
    {
        $AuthGroupModel = new AuthGroupModel();
        $rules = $AuthGroupModel->where('id', $id)->column('rules');
      
        $ids = explode(',', $rules[0]);
        $AuthRuleModel = new AuthRuleModel();
        $list = $AuthRuleModel->where('id', 'in', $ids)->select();
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $list);
    }

    //分配角色权限
    public function addMenus($id)
    {
        $params = $this->request->put();
        $menuIds = $params['menuIds']?? [];

        $data['id'] = $id;
        $data['rules'] = implode(',', $menuIds);
     
        $AuthGroupModel = new AuthGroupModel();
        $res = $AuthGroupModel->allowField(true)->isUpdate()->save($data);
        if (!$res) {
            return ajax_return(ResultCode::E_DB_ERROR, '操作失败!');
        } 
        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $groupUserIds = $AuthGroupAccessModel->where('group_id',$data['id'])->column('uid');
        foreach ($groupUserIds as $uid) {
            Cache::tag('menu')->rm($uid);
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }

    //查询角色用户列表
    public function userList($id)
    {
        $params = $this->request->put();
        $page = $params['page']?? '1';
        $size = $params['size']?? '10';
        $filters = $params['filters'];
        $keyword = $filters['keyword'];
        $where = [];
        if (!empty($keyword)) {
            $where[] = ['nickname|mobile|email', 'like', '%'.$keyword.'%'];
        }

        $AuthGroupAcessModel = new AuthGroupAccessModel();
        $uids = $AuthGroupAcessModel->where('group_id', $id)->column('uid');
      
        //查找符合条件的用户
        $where[] = ['id', 'in', $uids];
        $fields = 'id,nickname,sex,mobile,email,head_url,qq,weixin,referee,register_time,register_ip,from_referee,entrance_url,last_login_time,last_login_ip';
        $UserModel = new UserModel();
        $list = $UserModel->where($where)->field($fields)->paginate($size, false, ['page'=>$page])->toArray();
     
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($list['data'], 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }
}