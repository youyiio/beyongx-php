<?php
namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 地址信息模型
 */
class GeographyAreaModel extends Model
{
    protected $name = CMS_PREFIX . 'geography';

    const DISPLAY_FLAG_HIDE      = 0; //0.不显示;
    const DISPLAY_FLAG_SHOW      = 1; //1.显示;
    const DISPLAY_FLAG_JUMP_DOWN = 2; //2.忽略本级,跳到下级

    //自动完成
    protected $auto   = [];
    protected $insert = [];
    protected $update = [];

    //获取地址名称
    public static function getAreaName($areaCode)
    {
        if (empty($areaCode)) {
            return '未知地址';
        }

        $areaData = Cache::remember('area_name_list', function () {
            $GeographyAreaModel = new GeographyAreaModel();
            return $GeographyAreaModel->column('area_code,area_name,parent_code', 'area_code');
        }, 3600);

        return isset($areaData[$areaCode]) ? $areaData[$areaCode]['area_name'] : '未知地址';
    }

    //获取地址信息
    public static function getAreaInfo($areaCode, $fieldStyle = 0)
    {
        if (empty($areaCode)) {
            return null;
        }

        $areaData = self::get($areaCode);

        return !empty($areaData) ? parse_fields($areaData->toArray(), $fieldStyle) : null;
    }

}
