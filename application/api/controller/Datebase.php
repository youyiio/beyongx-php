<?php
namespace app\api\controller;

use think\Config;
use think\Db;

class Datebase extends Base
{
    public function tables()
    {
        $params = $this->request->put();
        $page = $params['page']?? 1;
        $size = $params['size']?? 10;
        $filters = $params['filters']?? '';

        $one = ($page-1) * $size;
        $fields = 'TABLE_NAME,TABLE_TYPE';
        //获取所有的表
        $sql = "select * from information_schema.TABLES where TABLE_SCHEMA=(select database()) limit {$one},{$size}";
      
        $re = Db::query($sql);
        halt($re);
        $d = config::get('database')['database'];
        $s = 'Tables_in_' . $d;
        //转换为索引数组
        $data = [];
        foreach ($re as $index => $item) {
            $r = strpos($item[$s], 'fa_basic');
                       if($r !== false){
            array_push($data, $item[$s]);
                        }
        }
        rsort($data);
    }
}
