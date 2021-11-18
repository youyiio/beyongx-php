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

    public function getFullImageUrlAttr($value, $data)
    {
        $switch = get_config('oss_switch');
        if ($switch !== 'true') {
            $fullImageUrl = url_add_domain($data['url_path']);
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
     * @param $imageIds，string|array
     * @return array
     */
    public static function getImages($imageIds)
    {
        if (empty($imageIds)) {
            return [];
        }
        if (is_string($imageIds)) {
            $imageIds = json_decode($imageIds, true);
        }
        if (!is_array($imageIds)) {
            return [];
        }

        $ImageModel = new ImageModel();
        $data = $ImageModel->where([['id','in', $imageIds]])->select();
        if (empty($data)) {
            return [];
        }
        $res = [];
        foreach ($data as $v) {
            $res[] = [
                'id'           => $v->id,
                'thumb_image_url'    => $v->thumb_image_url,
                'image_url'       => $v->url,
                'full_image_url'   => $v->url_path,
                //'image_url'       => $v->image_url,
                //'full_image_url'   => $v->full_image_url,
                //'full_thumb_image_url' => $v->full_thumb_image_url,
                'remark' => $v->remark,
            ];
        }
        return $res;
    }

}
