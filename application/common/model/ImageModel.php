<?php

/**
 * Created by VSCode.
 * User: cattong
 * Date: 2017-04-19
 * Time: 17:50
 */

namespace app\common\model;

use think\Model;

class ImageModel extends Model
{
    protected $name = 'sys_file';
    protected $pk = 'id';

    protected $type = [
        'id'    => 'integer',
        'create_time' => 'datetime',
    ];

    public function getFullFileUrlAttr($value, $data)
    {
        $switch = get_config('oss_switch'); //get_config('oss_switch');
        if ($switch !== 'true') {
            $fullImageUrl = url_add_domain($data['file_url']);
            $fullImageUrl = str_replace('\\', '/', $fullImageUrl);
        } else {
            $fullImageUrl = $data['oss_url'];
        }

        return $fullImageUrl;
    }
    
    public function getFullImageUrlAttr($value, $data)
    {
        $switch = get_config('oss_switch');
        if ($switch !== 'true') {
            $fullImageUrl = url_add_domain($data['file_url']);
            $fullImageUrl = str_replace('\\', '/', $fullImageUrl);
        } else {
            $fullImageUrl = $data['oss_url'];
        }

        return $fullImageUrl;
    }

    public function getFullThumbImageUrlAttr($value, $data)
    {
        $switch = get_config('oss_switch');
        if ($switch !== 'true') {
            $fullThumbImageUrl = url_add_domain($data['thumb_image_url']);
            $fullThumbImageUrl = str_replace('\\', '/', $fullThumbImageUrl);
        } else {
            $fullThumbImageUrl = $data['oss_url'];
        }

        return $fullThumbImageUrl;
    }

    /**
     * 获取取图片
     * @param $ids，string|array
     * @return array
     */
    public static function getImages($ids)
    {
        if (empty($ids)) {
            return [];
        }
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        if (!is_array($ids)) {
            return [];
        }

        $ImageModel = new ImageModel();
        $data = $ImageModel->where([['id', 'in', $ids]])->select();
        if (empty($data)) {
            return [];
        }
        $res = [];
        foreach ($data as $v) {
            $res[] = [
                'id'           => $v->id,
                'file_url'     => $v->file_url,
                'thumb_image_url'    => $v->thumb_image_url,
                'image_url'       => $v->file_url,
                'full_image_url'   => url_add_domain($v->file_path),
                'remark' => $v->remark,
            ];
        }
        return $res;
    }
}
