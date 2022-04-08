<?php

namespace app\common\model;


class TemplateMsgModel extends BaseModel
{
    protected $name = 'sys_template_msg';
    protected $pk = 'id';

    const STATUS_DELETE = -1;  //删除
    const STATUS_INVALID = 0;  //失效
    const STATUS_VALID = 1;  //生效
}
