<?php
namespace app\common\model;

use app\api\library\RolePermission;
use think\facade\Env;
use think\facade\Cache;

/**
 * 权限规则model
 */
class MenuModel extends BaseModel
{
    protected $name = 'sys_menu';

    public static function init()
    {
        MenuModel::afterInsert(function($menu){
            Cache::clear('menu');
        });
        MenuModel::afterUpdate(function($menu){
            Cache::clear('menu');
        });
        MenuModel::afterDelete(function($menu){
            Cache::clear('menu');
        });
    }

    //关联角色表
    public function roles()
    {
        return $this->belongsToMany('RoleModel', config('database.prefix') . 'sys_role_menu', 'role_id', 'menu_id');
    }

    //关联中间表 roleMenuModel
    public function roleMenus()
    {
        return $this->hasMany('roleMenuModel', 'menu_id', 'id');
    }

    /**
     * 删除数据
     * @param    array $map where语句数组形式
     * @return   boolean   操作是否成功
     * @throws \Exception
     */
	public function deleteData($map)
    {
		$count = $this
			->where('pid', $map['id'])
			->count();
		if ($count != 0) {
			return false;
		}
		$result = $this->where($map)->delete();
		return $result;
	}

    /**
     *
     * @param string $type tree获取树形结构 level获取层级结构
     * @param string $order 排序规则列名
     * @param string $name 值对应的列名
     * @param string $fieldPK 主键列名
     * @param string $filedPid 父节点的列名
     * @param string $belongsTo 归属标识
     * @return array|\PDOStatement|string|\think\Collection 结构数据[
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTreeDataBelongsTo($type='tree', $order='sort', $name='name', $fieldPK='id', $filedPid='pid', $belongsTo='')
    {
        $where = [
            'belongs_to' => $belongsTo
        ];
        // 判断是否需要排序
        if (empty($order)) {
            $data = $this->where($where)->select();
        } else {
            $data = $this->where($where)->order('sort is null,' . $order)->select();
        }
        $data = $data->toArray();
        // 获取树形或者结构数据
        $tree = new \beyong\commons\data\Tree();
        if ($type == 'tree') {//供给如下拉菜单使用
            $data = $tree::tree($data, $name, $fieldPK, $filedPid);
        } else if ($type == "level") {//给左测菜单使用
            $data = $tree::channelLevel($data,0,'&nbsp;', $fieldPK);

            $auth = new RolePermission();
            //清理不显示的菜单
            foreach ($data as $k => $v) {
                //是否菜单
                if ($v['is_menu'] != 1) {
                    unset($data[$k]);
                    unset($data['_data']);
                    continue;
                }
             
                //是否有权限
                if (!$auth->checkPermission(session('uid'), strtolower($v['path']), 'admin')) {
                    unset($data[$k]);
                    unset($data['_data']);
                    continue;
                }

                foreach ($v['_data'] as $m => $n) {
                    if ($n['is_menu'] != 1) {
                        unset($data[$k]['_data'][$m]);
                        unset($data[$k]['_data'][$m]['_data']);
                        continue;
                    }
                    if (!$auth->checkPermission(session('uid'), strtolower($v['path']), 'admin')) {
                        unset($data[$k]['_data'][$m]);
                        unset($data[$k]['_data'][$m]['_data']);
                        continue;
                    }
                    foreach ($n['_data'] as $o => $p) {
                        if ($p['is_menu'] != 1) {
                            unset($data[$k]['_data'][$m]['_data'][$o]);
                            unset($data[$k]['_data'][$m]['_data'][$o]['_data']);
                            continue;
                        }
                        if (!$auth->checkPermission(session('uid'), strtolower($v['path']), 'admin')) {
                            unset($data[$k]['_data'][$m]['_data'][$o]);
                            unset($data[$k]['_data'][$m]['_data'][$o]['_data']);
                            continue;
                        }
                    }
                }
            }
        }
        //dump($data);die;
        return $data;
    }

}
