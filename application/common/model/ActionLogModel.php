<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-05-29
 * Time: 15:00
 */

namespace app\common\model;

/**
 * 操作日志模型
 */
class ActionLogModel extends BaseModel
{
    protected $name =  CMS_PREFIX . 'action_log';
    protected $pk = 'id';

    const ACTION_LOGIN = 'login'; //登陆
    const ACTION_LOGOUT = 'logout'; //退出
    const ACTION_CHECK_IN = 'check_in'; //签到

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = [];
}