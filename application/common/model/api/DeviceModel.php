<?php
namespace app\common\model\api;

use app\common\model\BaseModel;

/**
 * 设备模型
 */
class DeviceModel extends BaseModel
{
    protected $name = 'api_device';
    protected $pk = 'device_id';

    protected $auto   = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    //是否存在设备
    public function isExist($deviceId = '', $client = [])
    {
        if (empty($deviceId)) {
            return $this->error = '设备ID为空';
        }
        $isNew = $this->where('device_id', $deviceId)->count();
        if ($isNew >= 1) {
            return true;
        }

        return false;
    }

    //新增设备
    public function addDevice($client = [])
    {
        $client = empty($client) ? get_client_info() : $client;
        if (empty($client)) {
            return $this->error = '设备信息为空';
        }

        $res = $this->validate('device.add')->isUpdate(false)->save($client);
        if (!$res) {
            return false;
        }

        return true;
    }

}
