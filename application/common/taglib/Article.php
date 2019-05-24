<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-05-23
 * Time: 14:53
 */

namespace app\common\taglib;

use think\template\TagLib;

class Article extends TagLib
{
    protected $xml  = 'article';

    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close表示是否需要闭合（false表示不需要，true表示需要， 默认false） alias 标签别名 level 嵌套层次
        //cache：是否缓冲，值true,false,int(秒); cid：分类id; result|ids|assign:结果的返回值，赋值给相应的变量; id:定义循环或结果的变量；
        'view' => ['attr' => 'aid,id,assign', 'close' => true],
        'categorys'  => ['attr' => 'cache,cid,ids,id,assign,limit', 'close' => true],
        'list'      => ['attr' => 'cid,cname,cache,page-size,id,assign', 'close' => true],
        'search'  => ['attr' => 'cid,keyword,id', 'close' => true],
        'hotlist' => ['attr' => 'cid,cache,limit,id', 'close' => true],
        'latestlist' => ['attr' => 'cid,cache,limit,id', 'close' => true],
        'relatedlist' => ['attr' => 'aid,cid,cache,limit,id', 'close' => true],
    ];

    /**
     * 查询文章分类列表,cid有值，获取二级分类
     * {article:categorys cache='true' id='vo'} {/article:categorys}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagCategorys($tag, $content)
    {
        $categoryId = empty($tag['cid']) ? 0 : $tag['cid'];
        $defaultCache = 30 * 16;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $ids = empty($tag['ids']) ? '_ids' : $tag['ids'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $limit = empty($tag['limit']) ? 0 : $tag['limit'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $categoryId = $this->autoBuildVar($categoryId);
        $ids = $this->autoBuildVar($ids);
        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);
        $limit = $this->autoBuildVar($limit);

        $parse  = "<?php ";
        $parse .= "  \$cacheMark = 'categorys_' . $cache . $categoryId . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];";
        $parse .= "  \$where[] = ['pid' , '=', $categoryId];";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    \$CategoryModel = new \app\common\model\CategoryModel();";
        $parse .= "    $list = \$CategoryModel->where(\$where)->order('sort asc,id asc')->limit($limit)->select();";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $list, $cache);";
        $parse .= "    }";
        $parse .= "  } ";
        $parse .= "  $ids = $list;";
        $parse .= "  ?>";

        $parse .= "  {volist name='$list' id='$id'} ";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 通过文章id，查询文章
     * {article:view aid='' id='vo' assign="article"}{$vo.title}....{/article:view}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagView($tag, $content)
    {
        $aid = empty($tag['aid']) ? 0 : $tag['aid'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $aid = $this->autoBuildVar($aid);
        $id = $this->autoBuildVar($id);
        $assign = $this->autoBuildVar($assign);

        $parse  = "<?php ";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  $id = \$ArticleModel->find(['article_id' => $aid]);";
        $parse .= "  $assign = $id; ";
        $parse .= "  ?>";
        $parse .= $content;

        return $parse;
    }

    /**
     * 查询文章列表，
     * {article:list cid='' cache='true' limit='10' id='vo'} {/article:list}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagList($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 30 * 16;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $pageSize = empty($tag['page-size']) ? 10 : $tag['page-size'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        //$cname = $this->autoBuildVar($cname);dump($cname);die('dd');
        $pageSize = $this->autoBuildVar($pageSize);
        $assign = $this->autoBuildVar($assign);
        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $parse  = "<?php ";
        $parse .= "  \$page = input('page/d', 1); ";
        $parse .= "  \$cid = $cid; ";
        $parse .= "  \$cname = '$cname';";
        $parse .= "  if (empty(\$cid) && !empty(\$cname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>\$cname])->find();";
        $parse .= "    if (!empty(\$category)) { \$cid = \$category['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'index_category_' . \$cid . '_' . $pageSize . '_' . \$page;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$targetFields = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    if (\$cid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild(\$cid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, \$cid);";
        $parse .= "      $list = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$targetFields)->order('is_top desc,sort,post_time desc')->paginate($pageSize,false,['query'=>input('param.')]);";
        $parse .= "    } else { ";
        $parse .= "      \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "      $list = \$ArticleModel->where(\$where)->field(\$targetFields)->order('is_top desc,sort,post_time desc')->paginate($pageSize,false,['query'=>input('param.')]);";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $list, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  $assign = $list;";
        $parse .= '  ?>';
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 关键词搜索
     * {article:search keyword='' id=''}{/article:search}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagSearch($tag, $content)
    {
        $categoryId = empty($tag['cid']) ? 0 : $tag['cid'];
        $keyword = empty($tag['keyword']) ? '' : $tag['keyword'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);//作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $keyword = $this->autoBuildVar($keyword);
        $categoryId = $this->autoBuildVar($categoryId);

        $parse  = '<?php ';
        $parse .= '  $where = [];';
        $parse .= '  $where[] = [\'status\', \'=\', \app\common\model\ArticleModel::STATUS_PUBLISHED];';

        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($categoryId) { ";
        $parse .= "    \$childs = \app\common\model\CategoryModel::getChild($categoryId);";
        $parse .= "    \$cids = \$childs['ids'];";
        $parse .= "    array_push(\$cids, $categoryId);";
        $parse .= "    $list = \$ArticleModel->has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->whereLike('title','%'.$keyword.'%','and')->field('id,title,thumb_image_id,description,author,post_time')->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);";
        $parse .= "  } else { ";
        $parse .= "    $list = \$ArticleModel->where(\$where)->whereLike('title','%'.$keyword.'%','and')->field('id,title,thumb_image_id,description,author,post_time')->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);";
        $parse .= "  };";

        $parse .= "  ?>";
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 热门文章
     * {article:hotlist cid='' cache='true' limit='10' id='vo'} {/article:hotlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagHotlist($tag, $content)
    {
        $categoryId = empty($tag['cid']) ? 0 : $tag['cid'];
        $defaultCache = 30 * 16;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $categoryId = $this->autoBuildVar($categoryId);
        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $cache = $this->autoBuildVar($cache);
        $limit = $this->autoBuildVar($limit);

        $parse  = '<?php ';
        $parse .= "  \$cacheMark = 'article_hot_list_' . $categoryId . $cache . $limit;";
        $parse .= '  $where = [];';
        $parse .= '  $where[] = [\'status\', \'=\', \app\common\model\ArticleModel::STATUS_PUBLISHED];';
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    if ($categoryId) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($categoryId);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $categoryId);";
        $parse .= "      $list  = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $list = \$ArticleModel->where(\$where)->field('id,title,description,author,post_time,read_count,thumb_image_id')->order('read_count desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $list, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?> ";
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 最新文章
     * {article:latestlist cid='' cache='true' limit='10' id='vo'} {/article:hotlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagLatestlist($tag, $content)
    {
        $categoryId = empty($tag['cid']) ? 0 : $tag['cid'];
        $defaultCache = 30 * 16;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $categoryId = $this->autoBuildVar($categoryId);
        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        //$cacheMark = $categoryId . '' . $cache . $limit;

        $parse  = "<?php ";
        $parse .= "  \$cacheMark = 'article_latest_list_' . $categoryId . $cache . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    if ($categoryId) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($categoryId);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $categoryId);";
        $parse .= "      $list = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $list = \$ArticleModel->where(\$where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $list, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?>";
        $parse .= "  {volist name='$list' id='$id' }";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 相关推荐文章列表
     * {article:relatedlist cid='' cache='true' limit='10' id='vo'} {/article:hotlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagRelatedlist($tag, $content)
    {
        $articleId = empty($tag['aid']) ? 0 : $tag['aid'];
        $categoryId = empty($tag['cid']) ? 0 : $tag['cid'];
        $defaultCache = 30 * 16;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式(为了处理标签传入时是表达式的情况）；
        $articleId = $this->autoBuildVar($articleId);
        $categoryId = $this->autoBuildVar($categoryId);
        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        //$cacheMark = $articleId . '' . $cache . $limit;

        //需要与外部交互或内嵌标签交互的变量，都不加\$；
        $parse  = "<?php ";
        $parse .= "  \$cacheMark = 'article_latest_list_' . $articleId . $cache . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['article_a_id', '=', $articleId];";
        $parse .= "  \$whereOr[] = ['article_b_id', '=', $articleId];";
        $parse .= "  \$dataList = db(CMS_PREFIX . 'article_data')->where(\$where)->whereOr(\$whereOr)->field('id,article_a_id,article_b_id,title_similar,content_similar')->order('title_similar desc,content_similar desc')->limit(100)->select();";
        $parse .= "  \$ids = [];";
        $parse .= "  foreach (\$dataList as \$articleData) {";
        $parse .= "    if (\$articleData['article_a_id'] == $articleId) {";
        $parse .= "      \$ids[] = \$articleData['article_b_id'];";
        $parse .= "    } else {";
        $parse .= "      \$ids[] = \$articleData['article_a_id'];";
        $parse .= "    }";
        $parse .= "  };";

        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$where[] = ['id', 'in', \$ids];";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    if ($categoryId) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($categoryId);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $categoryId);";
        $parse .= "      $list = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $list = \$ArticleModel->where(\$where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $list, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?>";
        $parse .= "  {volist name='$list' id='$id' }";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    function _randVarName($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ_';    //字符池
        $key = '';
        $count = strlen($pattern);
        for($i = 0; $i < $length; $i++) {
            if ($i == 0) {
                $key .= $pattern{mt_rand(10, $count - 1)};
            } else {
                $key .= $pattern{mt_rand(0, $count - 1)};    //生成php随机数
            }
        }

        return $key;
    }
}