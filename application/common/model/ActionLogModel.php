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

    const ACTION_LOGIN = 'login'; //登录
    const ACTION_LOGOUT = 'logout'; //登出
    const ACTION_CHECK_IN = 'check_in'; //签到
    const ACTION_ACCESS = 'access'; //浏览

    //自动完成
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = [];

    //属性：action_text
    public function getActionTextAttr($value, $data)
    {
        $action = [
            'login'    => '登录',
            'logout'   => '登出',
            'check_in' => '签到',
            'access'   => '浏览',
        ];
        return isset($action[$data['action']]) ? $action[$data['action']] : '未知';
    }

    //表连接：用户
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id', 'user_id');
    }
}