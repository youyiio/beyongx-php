<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-06-12
 * Time: 20:45
 */

namespace app\common\model;


class AddonsModel extends BaseModel
{
    protected $name = 'sys_addons';

    const STATUS_DELETED = -1; //已删除
    const STATUS_UNKNOWN = 0;  //未知状态
    const STATUS_DOWNLOADING = 1;  //下载中
    const STATUS_DOWNLOADED = 2; //已下载
    const STATE_INSTALLING = 3; //安装中
    const STATE_INSTALLED = 4; //已安装
    const STATE_UNINSTALLING = 5; //卸载中
    const STATE_UNINSTALLIED = 6; //已卸载

    //属性：status_text
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            -1 => '删除',
            0 => '未知状态',
            1 => '下载中',
            2 => '已下载',
            3 => '安装中',
            4 => '已安装',
            5 => '卸载中',
            6 => '已卸载',
        ];
        return isset($status[$data['status']])?$status[$data['status']] : '未知';
    }
}