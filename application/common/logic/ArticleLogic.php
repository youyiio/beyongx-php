<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-02-09
 * Time: 11:11
 */

namespace app\common\logic;


use app\common\model\cms\ArticleModel;
use think\Model;

class ArticleLogic extends Model
{

    public function getHotList($pageIndex=1,$pageSize=10)
    {
        $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();
        return $list;
    }

    public function getRecommendList($keyword,$pageIndex=1, $pageSize=10)
    {
        $keywords = explode(',', $keyword);
        $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
        $where[] = ['keywords', 'like', $keywords];
        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();

        if (count($list) == 0) {
            unset($where);
            $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
            $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();
        }

        return $list;
    }
}