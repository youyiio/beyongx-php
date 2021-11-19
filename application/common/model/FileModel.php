<?php
/**
 * Created by VSCode.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 14:55
 */

namespace app\common\model;


use think\Model;

class FileModel extends Model
{
    protected $name = 'sys_file';

    protected $pk = 'id';

    public function getFullFileUrlAttr($value, $data)
    {
        $switch = 'false';//get_config('oss_switch');
        if ($switch !== 'true') {
            $fullImageUrl = url_add_domain($data['url_path']);
            $fullImageUrl = str_replace('\\', '/', $fullImageUrl);
        } else {
            $fullImageUrl = $data['oss_url'];
        }

        return $fullImageUrl;
    }
}