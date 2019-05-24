<?php
namespace app\admin\controller;

use app\common\model\ConfigModel;
use app\common\model\LinksModel;
use think\Controller;
use think\Db;
use think\facade\Cache;
use think\facade\Env;

/**
* 系统设置控制器
*/
class System extends Base
{
    //系统设置
    public function index()
    {
        $tab = input('get.tab', 'base');
        $this->assign('tab', $tab);

        if (request()->isAjax()) {
            $data = input('param.');
            unset($data['s']); //路由地址参数不做处理；

            $ConfigModel = new ConfigModel();
            foreach ($data as $k => $v) {
                if (ConfigModel::get($k)) {
                    $ConfigModel->where('name',$k)->setField('value', $v);
                } else {
                    $ConfigModel->save(['name' => $k, 'value' => $v]);
                }
            }
            cache('config', null);
            $this->success('设置成功');
        }

        //获取标签组
        $ConfigModel = new ConfigModel();
        $tabMeta = $ConfigModel->where('name', 'tab_meta')->value('value');
        $tabs = json_decode($tabMeta, true);
        $this->assign('tabs', $tabs);

        $configs = $ConfigModel->where('tab', '=', $tab)->order('sort asc')->select();
        $this->assign('configs', $configs);

        return $this->fetch('index');
    }

    //联系信息
    public function contact()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $ConfigModel = new ConfigModel();
            foreach ($data as $k => $v) {
                $ConfigModel->where('name',$k)->setField('value', $v);
            }
            cache('config',null);
            $this->success('设置成功');
        }

        return view();
    }

    //邮箱设置
    public function email()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $ConfigModel = new ConfigModel();
            foreach ($data as $k => $v) {
                $ConfigModel->where('name',$k)->setField('value',$v);
            }
            cache('config',null);
            $this->success('设置成功');
        }

        return view();
    }

    //SEO设置
    public function seo()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            $ConfigModel = new ConfigModel();
            foreach ($data as $k => $v) {
                $ConfigModel->where('name',$k)->setField('value',$v);
            }
            cache('config',null);
            $this->success('设置成功');
        }

        return view();
    }

    //清理缓存
    public function clearCache()
    {
        if (request()->isPost()) {
            $dir = new \youyi\util\Dir(Env::get('runtime_path'));
            $types = input('types');
            if (count($types)) {
                foreach($types as $k=>$v) {
                    switch($v) {
                        case 'temp':
                            is_dir(Env::get('runtime_path') . 'temp') && $dir->delDir(Env::get('runtime_path') . 'temp');
                            break;
                        case 'data':
                            if (config('cache.type') == 'File') {
                                is_dir(Env::get('runtime_path') . 'cache') && $dir->delDir(Env::get('runtime_path') . 'cache');
                            } elseif (config('cache.type') == 'Redis') {
                                Cache::clear();
                            }

                            break;
                        case 'log':
                            is_dir(Env::get('runtime_path') . 'log') && $dir->delDir(Env::get('runtime_path') . 'log');
                            break;
                        case 'vars':
                            //删除自定义的缓存，已经的缓存变量
                            //is_dir(Env::get('runtime_path') . 'cache') && $dir->delDir(Env::get('runtime_path') . 'cache');
                            Cache::rm('menu' . session('uid'));
                            Cache::rm('config');
                            break;
                        default:
                            break;
                    }
                }
            }

            $this->success("清除缓存成功！");
        }

        return view('clearCache');
    }

    //广告位设置
    public function ad()
    {
        if (request()->isAjax()) {

        }

        return view();
    }

    //友情链接设置
    public function links()
    {
        $LinksModel = new LinksModel();
        $list = $LinksModel->order('sort')->select();
        $this->assign('list',$list);
        return view();
    }

    //新增友链
    public function addLinks()
    {
        $data = input('post.');
        $LinksModel = new LinksModel();
        $res = $LinksModel->insert($data);
        if ($res) {
            cache('links',null);
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    //编辑友链
    public function editLinks()
    {
        $data = input('post.');

        $res = LinksModel::update($data);
        if ($res) {
            cache('links',null);
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    //排序友链
    public function orderLinks()
    {
        $data = input('post.');
        $LinksModel = new LinksModel();
        foreach ($data as $k => $v) {
            $LinksModel->where('id',$k)->setField('sort',$v);
        }
        cache('links',null);
        $this->success('成功排序');
    }

    //删除友链
    public function deleteLinks()
    {
        $id = input('param.id/d',0);
        $LinksModel = new LinksModel();
        $res = $LinksModel->where('id',$id)->delete();
        if ($res) {
            cache('links',null);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
