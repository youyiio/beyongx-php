<?php
namespace app\common\model;


class UserMetaModel extends BaseMetaModel
{
    protected $name = 'sys_user_meta';

    //读取|设置meta值
    public function _meta($fkId, $metaKey='', $metaValue='', $mode=BaseModel::MODE_SINGLE_VALUE)
    {
        $fk = 'target_id';
        $where = [
            $fk => $fkId,
            'meta_key' => $metaKey
        ];

        //全部清除模式
        if ($metaValue === null && $mode == BaseMetaModel::MODE_MULTIPLE_VALUE) {
            $this->where($where)->delete();
            return true;
        }

        //写模工下，且为多值情况时，增加查询条件
        if ($metaValue !== '' && $metaValue !== null && $mode == BaseModel::MODE_MULTIPLE_VALUE) {
            $where['meta_value'] = $metaValue;
        }

        $meta = $this->where($where)->find();
        if ($meta) {
            if ($metaValue === '') {
                return $meta['meta_value'];
            } else if ($metaValue === null) {
                $this->where('id', $meta['id'])->delete();
            } else {
                $data = [
                    'meta_key' => $metaKey,
                    'meta_value' => $metaValue,
                    'update_time' => date_time()
                ];
                $this->where('id', $meta['id'])->setField($data);
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
                $data['update_time'] = $data['create_time'];
                $this->insert($data);
            }
        }
    }

    //读取metas多值
    public function _metas($fkId, $metaKey='')
    {
        $fk = 'target_id';
        $where = [
            $fk => $fkId,
        ];
        if ($metaKey !== '') {
            $where['meta_key'] = $metaKey;
        }

        return $this->where($where)->column('meta_value');
    }
}