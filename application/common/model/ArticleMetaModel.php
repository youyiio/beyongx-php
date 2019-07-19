<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-11-22
 * Time: 18:24
 */

namespace app\common\model;


class ArticleMetaModel extends BaseMetaModel
{
    protected $name = CMS_PREFIX . 'article_meta';

    const KEY_TIMING_POST = '__timing_post__'; //定时发布
    const KEY_BAIDU_INDEX = 'baidu_index'; //百度索引key

    protected $auto = ['update_time'];
    protected $insert = ['create_time', 'update_time'];
    protected $update = ['update_time'];

    public function getMetasByArticleId($aid)
    {
        return $this->where(['article_id' => $aid])->select();
    }

    public function getMeta($aid, $metaKey)
    {
        return $this->where(['article_id' => $aid, 'meta_key' => $metaKey])->find();
    }

    //读取|设置meta值
    public function _meta($fkId, $metaKey='', $metaValue='')
    {
        $fk = 'article_id';
        $meta = $this->where([$fk => $fkId, 'meta_key' => $metaKey])->find();
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
                $data['update_time'] = date_time();
                $data['create_time'] = date_time();
                $this->insert($data);
            }
        }
    }

    //读取metas多值
    public function _metas($fkId, $metaKey='')
    {
        $fk = 'article_id';
        $where = [
            [$fk] => $fkId,
        ];
        if ($metaKey !== '') {
            $where[] = ['meta_key' => $metaKey];
        }

        return $this->where($where)->select();
    }
}