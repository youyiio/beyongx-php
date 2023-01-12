<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2019-08-05
 * Time: 14:57
 */

namespace app\cms\controller;

use app\frontend\controller\Base;

class Search extends Base
{

    //搜索词：q, 分页：p；路由为 search/:q/[:p] 模式
    //返回 list 结果集，page 分页
    public function index($q='', $p=1)
    {
        if (empty($q)) {
            $this->error('请输入搜索词!');
        }

        if (true) {
            $this->_searchFromDb($q, $p);
        } else {
            $this->_searchFromES($q, $p);
        }

        $this->assign('q', $q);

        return $this->fetch("search/index");
    }

    //从数据库搜索
    private function _searchFromDb($q='', $p='')
    {
        $where = [];
        $where[] = ['status', '=', \app\common\model\cms\ArticleModel::STATUS_PUBLISHED];

        $ArticleModel = new \app\common\model\cms\ArticleModel();
        $field = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';
        $order = 'is_top desc,sort,post_time desc';
        $pageConfig = [
            'var_page' => 'p', //设置分页变量是p
            'query' => input('param.'),
            'page' => $p, //设置分页值
        ];
        $resultSet = $ArticleModel->where($where)->whereLike('title','%' . $q . '%','and')->field($field)->order($order)->paginate(10,false, $pageConfig);

        $this->assign('list', $resultSet);
        $this->assign('page', $resultSet->render());
    }

    //从ElasticSearch搜索
    private function _searchFromES($q='', $p='')
    {

    }

    //记录用户搜索日志
    private function _searchLog($q='', $p=1)
    {

    }
}