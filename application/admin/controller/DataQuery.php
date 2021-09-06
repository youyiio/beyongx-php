<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2017-08-17
 * Time: 16:18
 */

namespace app\admin\controller;

use think\Log;

class DataQuery extends Base
{
    /**
     * @param string $format, 返回的格式，方便前台显示，支持:raw(原生不转化), weui,mui
     * @return \think\response\Json
     */
    public function areas($format = 'raw')
    {
        $areaCode = input('areaCode/s', '0');

        $where[] = ['parent_code', '=', $areaCode];
        $where[] = ['display_flag', '<>', GeographyAreaModel::DISPLAY_FLAG_HIDE];

        $GeographyAreaModel = new GeographyAreaModel();
        $resultSet  = $GeographyAreaModel->where($where)->select();
        $displaySet = [];
        foreach ($resultSet as $ga) {
            if ($ga->display_flag == GeographyAreaModel::DISPLAY_FLAG_JUMP_DOWN) {
                unset($where);
                $where[] = ['parent_code', '=', $ga->area_code];
                $where[] = ['display_flag', '<>', GeographyAreaModel::DISPLAY_FLAG_HIDE];
                $resultSet1  = $GeographyAreaModel->where($where)->select();
                $displaySet  = array_merge($displaySet, $resultSet1->toArray());
            }
        }

        if (!empty($displaySet)) {
            unset($resultSet);
            $resultSet = $displaySet;
        }
        //$resultSet = parse_fields($resultSet, 1);

        //判断格式
        if ($format == 'mui-picker') {
            $resultSet = DataQuery::convert2MUIPickerJson($resultSet, 'areaName', 'areaCode');
        } else if ($format == 'weui-select') {
            $resultSet = DataQuery::convert2WeUISelectJson($resultSet, 'areaName', 'areaCode');
        } else if ($format == 'weui-picker') {
            $resultSet = DataQuery::convert2WeUISelectJson($resultSet, 'areaName', 'areaCode');
        }

        $result['code'] = 1;
        $result['msg'] = 'ok';
        $result['data'] = $resultSet;

        return json($result);
    }

    public static function convert2WeUISelectJson($models, $titleItemName, $valueItemName)
    {
        $selectJson = [];
        foreach ($models as $model) {
            $vo['title'] = $model[$titleItemName];
            $vo['value'] = $model[$valueItemName];
            array_push($selectJson, $vo);
        }

        return $selectJson;
    }

    public static function convert2WeUIPickerJson($models, $valueItemName, $idItemName)
    {
        $pickerJson = [
            'textAlign' =>  'center',
            'values' => [],  //显示在input的value上
            //'displayValues' => [], //显示在弹出的picker上
            'ids' => [],
        ];
        foreach ($models as $model) {
            array_push($pickerJson['values'], $model[$valueItemName]);
            //array_push($pickerJson['displayValues'], $model[$valueItemName]);
            array_push($pickerJson['ids'], $model[$idItemName]);
        }

        return $pickerJson;
    }

    public static function convert2MUIPickerJson($models, $textItemName, $valueItemName)
    {
        $pickerJson = [
          [
            'value' => '',  //显示在picker上的项的值
            'text' => '',   //显示在picker上的项的文本
              'children' => [], //级联的项内容
          ],
        ];

        $pickerJson = [];
        foreach ($models as $model) {
            $item = [];
            $item['value'] = $model[$valueItemName];
            $item['text'] = $model[$textItemName];

            array_push($pickerJson, $item);
        }

        return $pickerJson;
    }
}