<?php
namespace app\common\model;

use think\facade\Env;
use think\Model;

class BaseModel extends Model
{
    //Model定义规范，统一模型定义为XxxModel,初始化建议使用$Xxx = new XxxModel()；
    //1、定义$name, $pk属性；
    //2、常量定义: const STATUS_DELETED = -1;
    //3、自动完成或处理字段：$auto=[], $insert=[], $update=[], 相关字段需要设置setXxxAttr($value, $data)函数
    //4、新增属性定义：getXxxAttr($value, $data)
    //5、表关联：xxx()|xxxs() { return $this->hasOne, belongsTo,hasMany,belongsToMany;}
    //6、模型相关的操作方法及业务逻辑，如无要，尽量不要写在模型类中


    const MODE_SINGLE_VALUE = 1; //单值模式
    const MODE_MULTIPLE_VALUE = 2; //多值模式

    //字段自动完成或默认处理：create_time
    protected function setCreateTimeAttr($value, $data)
    {
        if (isset($data['create_time']) && !empty($date['create_time'])) {
            return $data['create_time'];
        } else {
            return date_time();
        }
    }

    //字段自动完成或默认处理：update_time
    protected function setUpdateTimeAttr($value, $data)
    {
        if (isset($data['update_time']) && !empty($date['update_time'])) {
            return $data['update_time'];
        } else {
            return date_time();
        }
    }

    //表扩展列：ext；需要表级字段支持【使用场景：扩展表model业务中常用的字段】
    //@deprecated
    public function ext($key, $value='')
    {
        $fields = $this->getTableFields();
        if (!array_key_exists('ext', $fields)) {
            die($this->getTable() . ' 表未支持ext字段！');
        }

        $ext = $this->ext;
        if (empty($ext)) {
            $exts = array();
        } else {
            $exts = json_decode($ext, true);
        }

        if ($value === '') {
            return isset($exts[$key]) ? $exts[$key] : null ;
        } else if ($value === null) {
            unset($exts[$key]);
        } else {
            $exts[$key] = $value;
        }

        $pk = $this->getPk();
        $pkVal = $this->$pk;
        $this->where($pk, $pkVal)->setField('ext', json_encode($exts));
    }

    //meta扩展表
    public function meta($metaKey, $metaValue='', $mode=BaseModel::MODE_SINGLE_VALUE)
    {
        $pk = $this->pk;

        //dump(substr(get_class($this), 0, -5));
        $model = substr(get_class($this), 0, -5)  . 'MetaModel';
        $MetaModel = new $model;
        if ($metaValue === '') {
            //属性已经有值时，直接返回
            if (isset($this->$metaKey)) {
                return $this->$metaKey;
            }

            $this->$metaKey = $MetaModel->_meta($this->$pk, $metaKey);
            return $this->$metaKey;
        }

        isset($this->$metaKey) ? $this->$metaKey = null : false;
        $MetaModel->_meta($this->$pk, $metaKey, $metaValue, $mode);
    }

    //meta扩展表
    public function metas($metaKey)
    {
        $pk = $this->pk;

        $model = substr(get_class($this), 0, -5)  . 'MetaModel';
        $MetaModel = new $model;

        $this->$metaKey = $MetaModel->_metas($this->$pk, $metaKey);
        return $this->$metaKey;
    }

    /**
     * 修改数据
     * @param   array   $map  where语句数组形式
     * @param   array   $data 数据 [k=>v]
     * @return  boolean  操作是否成功
     * @throws \Exception
     */
    protected function editData($map, $data)
    {
        // 去除键值首位空格
        foreach ($data as $k => $v) {
            $data[$k] = trim($v);
        }
        $result = $this->where($map)->setField($data);
        return $result;
    }

    /**
     * 数据排序,更新排序字段
     * @param  array $data   数据源
     * @param  string $pk    主键
     * @param  string $orderField 排序字段
     * @return boolean      操作是否成功
     */
    public function orderData($data, $pk = 'id', $orderField = 'sort')
    {
        foreach ($data as $k => $v) {
            $v = empty($v) ? null : $v;
            $this->where(array($pk => $k))->update(array($orderField => $v));
        }
        return true;
    }

    /**
     * 获取全部数据
     * @param  string $type tree获取树形结构 level获取层级结构
     * @param string $order 排序规则列名
     * @param string $name 值对应的列名
     * @param string $fieldPK 主键列名
     * @param string $fieldPid 父节点的列名
     * @return array 结构数据
     * @throws \Exception
     */
    public function getTreeData($type = 'tree', $order = '', $name='name', $fieldPK='id', $fieldPid='pid')
    {
        // 判断是否需要排序
        if (empty($order)) {
            $data = $this->select();
        } else {
            $data = $this->order($order . ' is null,' . $order)->select();
        }
        $data = $data->toArray();
        // 获取树形或者结构数据
        $tree = new \beyong\commons\data\Tree();
        if ($type == 'tree') {
            $data = $tree::tree($data, $name, $fieldPK, $fieldPid);
        } elseif ($type = "level") {
            $data = $tree::channelLevel($data, 0, '&nbsp;', $fieldPK);
        }
        return $data;
    }

    //大量数据导入
    public function bigDataInsertFromCsv($data, $replace = false)
    {
        debug('s5');
        $tempFile  = Env::get('runtime_path') . 'big_data_tmp.csv';
        $f         = new \SplFileObject($tempFile, 'w');
        $delimiter = ","; //分隔符
        $enclosure = '"'; //数据引号
        $fields    = []; //字段
        foreach ($data as $k => $v) {
            if ($k == 0) {
                $fields = array_keys($v);
            }
            $f->fputcsv($v, $delimiter, $enclosure);
        }
        //debug('s6');
        if ($replace) {
            $act = 'replace'; //替换重复值
        } else {
            $act = 'ignore'; //忽略重复值
        }
        $table = $this->getTable(); //获取表名
        if (empty($fields)) {
            $fields = $this->getQuery()->getTableInfo('', 'fields');
        }
        $fields = implode(',', $fields);
        $sql    = "load data infile '" . $tempFile . "' " . $act . " into table " . $table . " fields terminated by '" . $delimiter . "' enclosed by '" . $enclosure . "' lines terminated by '\n' (" . $fields . ");";
        return $this->execute($sql);
    }

    //数据导入
    public function bigDataInsert($data)
    {
        $fields = []; //字段
        $valStr = '';
        $table  = $this->getTable(); //获取表名
        foreach ($data as $k => $v) {
            if ($k == 0) {
                $fields = array_keys($v);
                $valStr .= '(' . implode(',', $v) . ')';
            } else {
                $valStr .= ',(' . implode(',', $v) . ')';
            }
        }
        $fields = implode(',', $fields);
        $sql    = 'insert into ' . $table . '(' . $fields . ') values ' . $valStr . ';';
        return $this->execute($sql);
    }

    //清空表
    public function truncate()
    {
        $table = $this->getTable(); //获取表名
        $sql   = 'truncate table ' . $table . ';';
        return $this->execute($sql);
    }
}
