<?php
namespace app\api\controller;

use think\Config;
use think\Db;

class Database extends Base
{
    public function tables()
    {
        $params = $this->request->put();
        $page = $params['page']?? 1;
        $size = $params['size']?? 10;
        $filters = $params['filters']?? '';

        $one = ($page-1) * $size;
        $fields = 'TABLE_NAME,TABLE_TYPE,TABLE_SCHEMA,ENGINE,VERSION,ROW_FORMAT,TABLE_ROWS,DATA_LENGTH,INDEX_LENGTH,TABLE_COMMENT,AUTO_INCREMENT,CREATE_TIME,UPDATE_TIME';
        //获取所有的表
        $sql = "select $fields from information_schema.TABLES where TABLE_SCHEMA=(select database())";
        $re = Db::query($sql);
        halt($re);
    }
}
