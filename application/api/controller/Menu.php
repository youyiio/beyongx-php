<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\MenuModel;
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
        if (!empty($keyword)) {
            $where[] = ['title', 'like', '%'.$keyword.'%'];
        }
        
        $MenuModel = new MenuModel();
        $list = $MenuModel->where($where)->order('id asc')->select();
     
        // 获取树形或者list数据
        $data = getTree($list, $pid, 'id', 'pid', $depth);
        if (isset($filters['struct']) && $filters['struct'] === 'list') {
            $data = getList($list, $pid, 'id', 'pid', $depth);
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

    //新增菜单
    public function create()
    {
        $params = $this->request->put();

        $validate = Validate::make([
            'pid' => 'require|integer',
            'name' => 'unique:'. config('database.prefix') . 'sys_menu,name',
            'title' => 'require',
            'type' => 'require|integer',
           
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!', $validate->getError());
        }
        
        $MenuModel = new MenuModel();
        $MenuModel->pid = $params['pid'];
        $MenuModel->sort = $params['sort']?? 1;
        $MenuModel->component = $params['component']?? '';
        $MenuModel->name = $params['name']?? '';
        $MenuModel->title = $params['title'];
        $MenuModel->path = $params['path']?? '';
        $MenuModel->permission = $params['permission']?? '';
        $MenuModel->is_menu = $params['isMenu']?? '';
        $MenuModel->icon = $params['icon']?? '';
        $MenuModel->type = $params['type'];
        $res = $MenuModel->save($params);
        $id = $MenuModel->id;

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }
        $returnData = MenuModel::get($id);
        
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //编辑菜单
    public function edit()
    {
        $params = $this->request->put();
        $validate = Validate::make([
            'id' => 'require',
            'name' => 'unique:'. config('database.prefix') . 'sys_menu,name',
            'title' => 'require',
            'type' => 'require|integer',
           
        ]);
        if (!$validate->check($params)) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!', $validate->getError());
        }

        $menu = MenuModel::get($params['id']);
        $res = $menu->isUpdate(true)->allowField(true)->save($params);
        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        $data = MenuModel::get($params['id']);
        $returnData = parse_fields($data, 1);
        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    //删除菜单
    public function delete($id)
    {
        $menu = MenuModel::get($id);
        $res = $menu->delete();

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!');
    }
}