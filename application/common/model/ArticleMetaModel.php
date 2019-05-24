<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-11-22
 * Time: 18:24
 */

namespace app\common\model;


class ArticleMetaModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'article_meta';

    const KEY_TIMING_POST = '__timing_post__'; //定时发布

    public function getMetasByArticleId($aid)
    {
        return $this->where(['article_id' => $aid])->select();
    }

    public function getMeta($aid, $metaKey)
    {
        return $this->where(['article_id' => $aid, 'meta_key' => $metaKey])->find();
    }
}