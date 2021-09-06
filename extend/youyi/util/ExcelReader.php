<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-03-22
 * Time: 16:46
 */

namespace youyi\util;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelReader
{

    public function readRows($filePath, $dataMetaArr=[], $hasTitle=true)
    {
        if (!file_exists($filePath)) {
            return '文件不存在!';
        }

        $ext = pathinfo($filePath,PATHINFO_EXTENSION); //文件后缀
        $ext = strtolower($ext);
        if (!in_array($ext, ['xls', 'xlsx'])) {
             return '文件后缀格式不正确!';
        }

        //判断excel表类型为2003还是2007
        if(strtolower($ext)=='xls') {
            $objReader = IOFactory::createReader('Excel5');
        } else if(strtolower($ext)=='xlsx') {
            $objReader = IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filePath);

        $objPHPExcel->setActiveSheetIndex(0);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        $excelData = array();
        $offset = $hasTitle ? 1 : 0;
        for ($row = 1 + $offset; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row - $offset - 1][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }

        if (count($dataMetaArr) == 0) {
            return $excelData;
        }

        if (count($dataMetaArr) !== $this->column_len($excelData)) {
            return '指定元组长度与数据长度不一对致';
        }

        $formatData = [];
        for($i = 0; $i < count($excelData); $i++) {
            $data = [];
            $j = 0;
            foreach ($dataMetaArr as $k) {
                $data[$k] = $excelData[$i][$j];
                $j++;
            }

            $formatData[] = $data;
        }

        return $formatData;
    }

    protected function column_len(&$arr)
    {
        if (!is_array($arr)) {
            return -1;
        }
        if (count($arr, COUNT_NORMAL) == 0) {//不统计多维数组
            return 0;
        }

        return count($arr[0]);
    }
}