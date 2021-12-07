<?php
namespace app\api\controller;

use app\common\library\ResultCode;
use think\Db;

class Database extends Base
{
    public function tables()
    {
        $params = $this->request->put();
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;
        $filters = $params['filters'] ?? '';

        $where = '';
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $key = parse_name($key, 0, true);
                $where .= "and $key like '%$value%'";
            }
        }
   
        $fields = 'TABLE_NAME,TABLE_TYPE,TABLE_SCHEMA,ENGINE,VERSION,ROW_FORMAT,TABLE_ROWS,DATA_LENGTH,INDEX_LENGTH,TABLE_COMMENT,AUTO_INCREMENT,CREATE_TIME,UPDATE_TIME';
        //获取所有的表
        $sql = "select $fields from information_schema.TABLES where TABLE_SCHEMA=(select database()) $where";
        $data = Db::query($sql);
    
        //分页
        $total = count($data);
        $pages = ceil($total / $size); 
        $start = ($page - 1) * $size;
        $records =  array_slice($data, $start, $size);
        //返回数据
        $returnData['current'] = $page;
        $returnData['pages'] = $pages;
        $returnData['size'] = $size;
        $returnData['total'] = $total;
        $returnData['records'] = $this->to_hump($records);

        return ajax_return(ResultCode::ACTION_SUCCESS, '操作成功!', $returnData);
    }

    public function to_hump($data)
    {
        if (empty($data) || !is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $val) {
            $tempVal = $data[$key];
            if ($tempVal && is_array($tempVal)) {
                $tempVal    = $this->to_hump($tempVal);
                $data[$key] = $tempVal;
            }

            $targetKey = strtolower($key);
            $targetKey = parse_name($targetKey, 1, true);
            
            if ($key === $targetKey) {
                continue;
            }

            unset($data[$key]);
            $data[$targetKey] = $tempVal;
        }

        return $data;
    }
}
