<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-11-22
 * Time: 9:42
 */

return [
    'auth_on' => 1, // 权限开关
    'auth_type' => 1, // 权限认证方式，1为实时认证；2为登录认证-缓存。
    'auth_group' => 'sys_auth_group', // 用户组数据表名, 不包含database.prefix
    'auth_group_access' => 'sys_auth_group_access', // 用户-用户组关系表, 不包含database.prefix
    'auth_rule' => 'sys_auth_rule', // 权限规则表, 不包含database.prefix
    'auth_user' => 'sys_user', // 用户信息表, 不包含database.prefix; 用于字段condition时，对值的判断
];