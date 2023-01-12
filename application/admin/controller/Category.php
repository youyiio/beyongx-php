<?php
namespace app\admin\controller;

use app\common\model\cms\CategoryModel;

/**
* 文章分类控制器
*/
class Category extends Base
{
    
    //文章分类
    public function index()
    {
        if (request()->isPost()) {
            $id = input('post.id', 0);
            $checked = input('post.checked', 'false');
            $category = CategoryModel::get($id);
            if (!$category) {
                $this->error('分类不存在!');
            }

            $msg = "";
            if ($checked == 'true') {
                $category->status = CategoryModel::STATUS_ONLINE;
                $msg = '分类上线成功!';
            } else {
                $category->status = CategoryModel::STATUS_OFFLINE;
                $msg = '分类下线成功!';
            }

            $category->save();

            $this->success($msg);
        }

        $CategoryModel = new CategoryModel();
        $list = $CategoryModel->getTreeData('tree','sort,id', 'title', 'id', 'pid');
        $this->assign('list', $list);

        return $this->fetch('category/index');
    }

    //新增分类
    public function addCategory()
    {
        //数据处理
        if (request()->isAjax()) {
            $data = input('post.');
            $CategoryModel = new CategoryModel();
            if (empty($data['id'])) {
                $res = $CategoryModel->isUpdate(false)->save($data);
            } else {
                $res = $CategoryModel->isUpdate(true)->save($data);
            }

            if ($res) {
                $this->success('操作成功', url('category/index'));
            } else {
                $this->error('操作失败');
            }
        }

        //获取默认排序
        $where = [];
        if (input('pid', 0)) {
            $where['pid'] = input('pid', 0);
        }
        $defaultSort = CategoryModel::where($where)->max('sort') + 1;
        $this->assign('defaultSort', $defaultSort);

        return $this->fetch('category/addCategory');
    }

    //分类排序
    public function orderCategory()
    {
        $data = input('post.');
        $arr = [];
        foreach ($data as $k => $v) {
            $arr[] = [
                'id' => $k,
                'sort' => empty($v) ? 0 : $v
            ];
        }
        $CategoryModel = new CategoryModel();
        $result = $CategoryModel->isUpdate(true)->saveAll($arr);
        if ($result) {
            $this->success('排序成功', url('category/index'));
        }else{
            $this->error('排序失败');
        }
    }

    //编辑分类
    public function editCategory($id)
    {
        $CategoryModel = new CategoryModel();
        $category = $CategoryModel->find($id);
        if (empty($category)) {
            $this->error('数据不存在');
        }

        $this->assign('category', $category);

        return $this->fetch('category/addCategory');
    }

    //删除分类
    public function deleteCategory($id)
    {
        $CategoryModel = new CategoryModel();
        $res = $CategoryModel->where('id', $id)->delete();
        if ($res) {
            $this->success('成功删除');
        } else {
            $this->error('删除失败');
        }
    }

}
