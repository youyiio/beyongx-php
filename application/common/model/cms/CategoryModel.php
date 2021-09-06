<?php
namespace app\common\model\cms;

use app\common\model\BaseModel;

class CategoryModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'category';

    protected $pk = 'id';

    const STATUS_OFFLINE  = 0;  //下线
    const STATUS_ONLINE   = 1;  //上线

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time', 'status' => self::STATUS_ONLINE];
    protected $update = [];

    //子类信息 返回 ['ids','list'];
    public static function getChild($cateId = 0)
    {
        $arrIds = [$cateId];
        $categoryList = self::where('status',CategoryModel::STATUS_ONLINE)->cache('category', 300)->order('pid')->column('*','id');
        $arrCate = [$categoryList];
        foreach ($categoryList as $k => $v) {
            if (in_array($v['pid'], $arrIds)) {
                array_push($arrIds, $v['id']);
                array_push($arrCate, $v);
            }
        }

        $child = [
            'ids' => $arrIds,
            'list' => $arrCate
        ];

        return $child;
    }

    //父类合集 返回 ['ids','list'];
    public static function getParent1($cateId = 0)
    {
        if ($cateId == 0) {
            return false;
        }
        $categoryList = self::where('status',CategoryModel::STATUS_ONLINE)->cache('category')->order('pid')->column('*','id');
        $arrIds = [$cateId];
        $arrCate = [$categoryList[$cateId]];
        $pid = $categoryList[$cateId]['pid'];
        while ($pid > 0) {
            array_push($arrIds, $pid);
            array_push($arrCate, $categoryList[$pid]);
            $pid = $categoryList[$pid]['pid'];
        }

        $parent = [
            'ids' => $arrIds,
            'list' => $arrCate
        ];

        return $parent;
    }

    //表自连接:父类
    public function parent()
    {
        return $this->hasOne('CategoryModel', 'id', 'pid');
    }

}