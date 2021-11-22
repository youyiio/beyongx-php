<?php 
namespace app\api\controller;

use app\common\library\ResultCode;
use app\common\model\MenuModel;
use app\common\model\UserModel;
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
        $list = $MenuModel->where($where)->order('id asc')->select()->toArray();
     
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
        
        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);

        $data = parse_fields($params);
        $data['create_time'] = date_time();
        $data['create_by'] = $userInfo['nickname']?? '';

        $MenuModel = new MenuModel();
        $res = $MenuModel->save($data);
        $id = $MenuModel->id;

        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        $menu = MenuModel::get($id);
        $returnData = parse_fields($menu->toArray(), 1);

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
        $user = $this->user_info;
        $userInfo = UserModel::get($user->uid);

        $params = parse_fields($params);
        $params['update_time'] = date_time();
        $params['update_by'] = $userInfo['nickname']?? '';

        $MenuModel = new MenuModel();
        $res = $MenuModel->isUpdate(true)->allowField(true)->save($params);
        if (!$res) {
            return ajax_return(ResultCode::ACTION_FAILED, '操作失败!');
        }

        $data = $MenuModel->where('id', '=', $params['id'])->select()->toArray();

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