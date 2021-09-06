<?php
namespace youyi\util;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelExporter
{
    /**
     * 导出excel
     * @param $titleArr 表头数组
     * @param $dataArr 数据数组
     * @param array $dataMetaArr 数据元数组，下标数组
     * @param string $savePath 保存路径
     * @param string $writerType 保存格式
     * @return string
     * @throws \Exception
     */
    public function exportExcel($titleArr, $dataArr, $dataMetaArr=[], $savePath='php://output', $writerType='xlsx')
    {
        if (!is_array($titleArr) || !is_array($dataArr)) {
             return '表格表头和数据须为数组!';
        }
        $dataColumnLen = $this->column_len($dataArr);
        if (count($titleArr) != $dataColumnLen) {
            return '表格表头和数据数组长度不一致!';
        }

        if (empty($dataMetaArr)) {
            $dataMetaArr = range(0,count($titleArr) - 1,1);
        }

        if (!in_array($writerType, ['xlsx', 'xls'])) {
            $writerType = 'xlsx';
        }

        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->setActiveSheetIndex(0);//设置当前sheet为第一个

        /*--------------表头信息插入Excel表中------------------*/
        $row = 1; //表行变量
        $activeSheet = $objPHPExcel->getActiveSheet();
        for ($i = 0; $i < count($titleArr); $i++) {
            $col = chr(ord('A') + $i);
            $activeSheet->setCellValue($col.$row, $titleArr[$i]);
        }

        /*--------------数据信息插入Excel表中------------------*/
        $row = 2;  //定义一个row变量
        foreach ($dataArr as $data) {
            for ($i = 0; $i < count($dataMetaArr); $i++) {
                $col = chr(ord('A') + $i);
                //$activeSheet->setCellValue($col . $row, $data[$dataMetaArr[$i]]);
                $activeSheet->setCellValueExplicit($col . $row, $data[$dataMetaArr[$i]], DataType::TYPE_STRING);
            }

            $row++;
        }


        $objPHPExcel->getActiveSheet()->setTitle('导出');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0); //设置sheet的起始位置
        if ($writerType == 'xls') {
            $PHPWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');   //通过IOFactory的写函数将上面数据写出来
        } else {
            $PHPWriter = IOFactory::createWriter( $objPHPExcel, "Excel2007");
        }

        //输出路径
        if (strtolower($savePath) == 'php://output') {
            if ($writerType == 'xls') {
                header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
            } else {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            }
            header('Cache-Control: max-age=0');//禁止缓存
            $fileName = 'excel_' . date('Y-m-d-Hi', time()) . '.' . $writerType;
            header("Content-Disposition: attachment;filename=\"$fileName\"");//告诉浏览器输出浏览器名称

            $PHPWriter->save("php://output"); //表示输出到下载流
        } else {
            $fileName = 'excel_' . date('Y-m-d-Hi', time()) . '.' . $writerType;
            $PHPWriter->save($savePath . DIRECTORY_SEPARATOR . $fileName); //表示在$path路径下面生成demo.xlsx文件
        }
    }

    /**
     * 从模板导出excel
     * @param $titleArr 表头数组
     * @param $dataArr 数据数组
     * @param array $dataMetaArr 数据元数组，下标数组
     * @param string $templateFile 模板路径
     * @param string $savePath 保存路径
     * @param string $writerType 保存格式
     * @return string
     * @throws \Exception
     */
    public function exportExcelFromTemplate($titleArr, $dataArr, $dataMetaArr=[], $templateFile, $savePath='php://output', $writerType='xlsx')
    {
        if (!is_array($titleArr) || !is_array($dataArr)) {
            return '表格表头和数据须为数组!';
        }
        $dataColumnLen = $this->column_len($dataArr);
        if (count($titleArr) != $dataColumnLen) {
            return '表格表头和数据数组长度不一致!';
        }

        if (empty($dataMetaArr)) {
            $dataMetaArr = range(0,count($titleArr) - 1,1);
        }

        if (!in_array($writerType, ['xlsx', 'xls'])) {
            $writerType = 'xlsx';
        }

        $phpExcelTpl = IOFactory::createReader('Excel5')->load($templateFile);
        $objPHPExcel = clone $phpExcelTpl;
        $objPHPExcel->setActiveSheetIndex(0);//设置当前sheet为第一个

        /*--------------表头信息插入Excel表中------------------*/
        $row = 1; //表行变量
        $activeSheet = $objPHPExcel->getActiveSheet();
        for ($i = 0; $i < count($titleArr); $i++) {
            $col = chr(ord('A') + $i);
            $activeSheet->setCellValue($col.$row, $titleArr[$i]);
        }

        /*--------------数据信息插入Excel表中------------------*/
        $row = 2;  //定义一个row变量
        foreach ($dataArr as $data) {
            for ($i = 0; $i < count($dataMetaArr); $i++) {
                $col = chr(ord('A') + $i);
                //$activeSheet->setCellValue($col . $row, $data[$dataMetaArr[$i]]);
                $activeSheet->setCellValueExplicit($col . $row, $data[$dataMetaArr[$i]], DataType::TYPE_STRING);
            }

            $row++;
        }


        $objPHPExcel->getActiveSheet()->setTitle('导出');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0); //设置sheet的起始位置
        if ($writerType == 'xls') {
            $PHPWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');   //通过PHPExcel_IOFactory的写函数将上面数据写出来
        } else {
            $PHPWriter = IOFactory::createWriter( $objPHPExcel, "Excel2007");
        }

        //输出路径
        if (strtolower($savePath) == 'php://output') {
            if ($writerType == 'xls') {
                header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
            } else {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            }
            header('Cache-Control: max-age=0');//禁止缓存
            $fileName = 'excel_' . date('Y-m-d-Hi', time()) . '.' . $writerType;
            header("Content-Disposition: attachment;filename=\"$fileName\"");//告诉浏览器输出浏览器名称

            $PHPWriter->save("php://output"); //表示输出到下载流
        } else {
            $fileName = 'excel_' . date('Y-m-d-Hi', time()) . '.' . $writerType;
            $PHPWriter->save($savePath . DIRECTORY_SEPARATOR . $fileName); //表示在$path路径下面生成demo.xlsx文件
        }
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