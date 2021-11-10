<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\RoleModel;
use think\Validate;

class Menu extends Base
{
    public function list()
    {
        $params = $this->request->put();
        $page = $params['page'];
        $size = $params['size'];

        $filters = $params['filters'];
        $keyword = $filters['keyword']?? '';
        $pid = $filters['pid']?? 0;
        $depth = $filters['depth']?? 1;

        $where = [];
        $where[] = ['belongs_to', '=', 'admin'];
        if (!empty($keyword)) {
            $where[] = ['title', 'like', '%'.$keyword.'%'];
        }
        
        $RoleModel = new RoleModel();
        $list = $RoleModel->where($where)->order('id asc')->paginate($size, false, ['page'=>$page])->toArray();
     
       
        // 获取树形或者list数据
        $data = getTree($list['data'], $pid, 'id', 'pid', $depth);
        if (isset($filters['struct']) && $filters['struct'] === 'list') {
            $data = getList($list['data'], $pid, 'id', 'pid', $depth);
        } 
        
        //返回数据
        $returnData['current'] = $list['current_page'];
        $returnData['pages'] = $list['last_page'];
        $returnData['size'] = $list['per_page'];
        $returnData['total'] = $list['total'];
        $returnData['records'] = parse_fields($data, 1);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //新增菜单
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'pid' => 'require|integer',
            'name' => 'require|unique:'. config('database.prefix') . 'sys_auth_rule,name',
            'title' => 'require',
            'type' => 'require|integer',
           
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!', $validate->getError());
        }
        
        $RoleModel = new RoleModel();
        $res = $RoleModel->save($params);
        $id = $RoleModel->id;
        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        $returnData = RoleModel::get($id);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑菜单
    public function edit()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'id' => 'require',
            'name' => 'require|unique:'. config('database.prefix') . 'sys_auth_rule,name',
            'title' => 'require',
            'type' => 'require|integer',
           
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!', $validate->getError());
        }

        $menu = RoleModel::get($params['id']);
     
        $menu->name = $params['name']?? '';
        $menu->title = $params['title']?? '';
        $menu->icon = $params['icon']?? '';
        $menu->type = $params['type']?? '';
        $menu->is_menu = $params['isMenu']?? '';
        $menu->sort = $params['sort']?? '';
        $menu->belongs_to = $params['belongsTo']?? '';
        $res = $menu->save();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        $data = RoleModel::get($params['id']);
        $returnData = parse_fields($data, 1);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //删除菜单
    public function delete($id)
    {
        $menu = RoleModel::get($id);
        $res = $menu->delete();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}