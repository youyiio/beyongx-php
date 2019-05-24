<?php
namespace app\common\model;

use think\facade\Env;

/**
 * 权限规则model
 */
class AuthRuleModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'auth_rule';

    public static function init()
    {
        AuthRuleModel::afterInsert(function($menu){
            Cache::clear('menu');
        });
        AuthRuleModel::afterUpdate(function($menu){
            Cache::clear('menu');
        });
        AuthRuleModel::afterDelete(function($menu){
            Cache::clear('menu');
        });
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
     * @param string $belongto 归属标识
     * @return array|\PDOStatement|string|\think\Collection 结构数据[
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTreeDataBelongto($type='tree', $order='sort', $name='name', $fieldPK='id', $filedPid='pid', $belongto='')
    {
        $where = [
            'belongto' => $belongto
        ];
        // 判断是否需要排序
        if (empty($order)) {
            $data = $this->where($where)->select();
        } else {
            $data = $this->where($where)->order('sort is null,' . $order)->select();
        }
        $data = $data->toArray();
        // 获取树形或者结构数据
        include_once(Env::get('root_path') . 'extend/' .'tree/Data.class.php');
        $tree = new \tree\Data;
        if ($type == 'tree') {//供给如下拉菜单使用
            $data = $tree::tree($data, $name, $fieldPK, $filedPid);
        } else if ($type == "level") {//给左测菜单使用
            $data = $tree::channelLevel($data,0,'&nbsp;', $fieldPK);

            $auth = new \think\auth\Auth();
            //清理不显示的菜单
            foreach ($data as $k => $v) {
                //是否菜单
                if ($v['is_menu'] != 1) {
                    unset($data[$k]);
                    unset($data['_data']);
                    continue;
                }
                //是否有权限
                if (!$auth->check($v['name'], session('uid'),1,'')) {
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
                    if (!$auth->check($n['name'], session('uid'),1,'')) {
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
                        if (!$auth->check($p['name'], session('uid'),1,'')) {
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
