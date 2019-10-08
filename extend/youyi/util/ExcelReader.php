<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-03-22
 * Time: 16:46
 */

namespace youyi\util;

use think\facade\Env;
use think\Loader;
use think\Log;

class ExcelReader
{

    public function readRows($filePath, $dataMetaArr=[], $hasTitle=true)
    {
        if (!file_exists($filePath)) {
            $this->error('文件不存在!');
        }

        $ext = pathinfo($filePath,PATHINFO_EXTENSION); //文件后缀
        $ext = strtolower($ext);
        if (!in_array($ext, ['xls', 'xlsx'])) {
             return '文件后缀格式不正确!';
        }

        //文件分析
        include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel.php';
        include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel/IOFactory.php';
        include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel/Writer/IWriter.php';
        include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel/Writer/Excel5.php';
        include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel/Writer/Excel2007.php';
        //include Env::get('root_path') . 'extend/' . 'PHPExcel/PHPExcel/Writer.Abstract';

        //判断excel表类型为2003还是2007
        if(strtolower($ext)=='xls') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } else if(strtolower($ext)=='xlsx') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filePath);

        $objPHPExcel->setActiveSheetIndex(0);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

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