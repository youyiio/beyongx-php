<?php
namespace app\common\model;

use think\Model;
use think\model\Pivot;

/**
 * 权限规则model
 */
class RoleMenuModel extends Pivot
{
    protected $name = 'sys_role_menu';

}
