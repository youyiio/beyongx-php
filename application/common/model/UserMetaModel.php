<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-06-03
 * Time: 14:31
 */

namespace app\common\model;


class UserMetaModel extends BaseMetaModel
{
    protected $name = CMS_PREFIX . 'user_meta';

    //读取|设置meta值
    public function _meta($fkId, $metaKey='', $metaValue='')
    {
        $fk = 'user_id';
        $meta = $this->where([$fk => $fkId, 'meta_key' => $metaKey])->find();
        if ($meta) {
            if ($metaValue === '') {
                return $meta['meta_value'];
            } else if ($metaValue === null) {
                $this->where('id', $meta['id'])->delete();
            } else {
                $this->where('id', $meta['id'])->setField(['meta_key'=>$metaKey, 'meta_value'=>$metaValue]);
            }
        } else {
            if ($metaValue === '') {
                return '';
            } else if ($metaValue === null) {
                return true;
            } else {
                $data[$fk] = $fkId;
                $data['meta_key'] = $metaKey;
                $data['meta_value'] = $metaValue;
                $data['create_time'] = date_time();
                $this->insert($data);
            }
        }
    }

    //读取metas多值
    public function _metas($fkId, $metaKey='')
    {
        $fk = 'user_id';
        $where = [
            [$fk] => $fkId,
        ];
        if ($metaKey !== '') {
            $where[] = ['meta_key' => $metaKey];
        }

        return $this->where($where)->select();
    }
}