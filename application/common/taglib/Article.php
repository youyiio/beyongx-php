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
        //cache：是否缓冲，值true,false,int(秒); cid：分类id; assign:结果的返回值,变量后续可使用，赋值给相应的变量; id:定义循环或结果的变量；
        'view' => ['attr' => 'aid,id,assign', 'close' => true],  //文章明细信息标签
        'prev'  => ['attr' => 'aid,cid,cname,id,assign', 'close' => true], //下一篇文章标签
        'next'  => ['attr' => 'aid,cid,cname,id,assign', 'close' => true], //上一篇文章标签
        'list'   => ['attr' => 'cid,cname,cache,page-size,id,assign', 'close' => true], //文章列表标签
        'search'  => ['attr' => 'cid,keyword,page-size,id,assign', 'close' => true], //搜索文章列表标签
        'hotlist' => ['attr' => 'cid,cname,cache,limit,id', 'close' => true], //热门文章列表标签
        'latestlist' => ['attr' => 'cid,cname,cache,limit,id', 'close' => true], //最新文章列表标签
        'relatedlist' => ['attr' => 'aid,cid,cname,cache,limit,id', 'close' => true], //相关推荐文章列表标签
        'randomlist' => ['attr' => 'cid,cname,cache,limit,id', 'close' => true], //随机文章列表标签
    ];


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

        //标签内局部变量
        $internalAid = '$_aid_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  $internalAid = $aid; ";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  $id = \$ArticleModel->find(['id' => $internalAid]);";
        $parse .= "  $assign = $id; ";
        $parse .= "  ?>";
        $parse .= $content;

        return $parse;
    }

    /**
     * 通过文章id，查询上一篇文章（比当前文章新,id比当前id大）
     * {article:prev aid='' id='vo' assign="article"}{$vo.title}....{/article:prev}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagPrev($tag, $content)
    {
        $aid = empty($tag['aid']) ? 0 : $tag['aid'];
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $aid = $this->autoBuildVar($aid);
        $cid = $this->autoBuildVar($cid);
        $id = $this->autoBuildVar($id);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalAid = '$_aid_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  $internalAid = $aid; ";
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $id = null;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['id','>', $internalAid];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) { ";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];} else { $internalCid = -1;}";
        $parse .= "  } ";
        $parse .= "  if ($internalCid) {";
        $parse .= "    \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "    \$cids = \$childs['ids'];";
        $parse .= "    array_push(\$cids, $internalCid);";
        $parse .= "    $id = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field('id,title,post_time,author')->order('id asc')->find();";
        $parse .= "  } else {";
        $parse .= "    \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "    $id = \$ArticleModel->where(\$where)->field('id,title,post_time,author')->order('id asc')->find();";
        $parse .= "  } ";
        $parse .= "  $assign = $id; ";
        $parse .= "  ?>";
        $parse .= $content;

        return $parse;
    }

    /**
     * 通过文章id，查询下一篇文章（比当前文章旧,id比当前id小）
     * {article:prev aid='' id='vo' assign="article"}{$vo.title}....{/article:prev}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagNext($tag, $content)
    {
        $aid = empty($tag['aid']) ? 0 : $tag['aid'];
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $aid = $this->autoBuildVar($aid);
        $cid = $this->autoBuildVar($cid);
        $id = $this->autoBuildVar($id);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalAid = '$_aid_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  $internalAid = $aid; ";
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $id = null;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['id','<', $internalAid];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) { ";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];} else { $internalCid = -1;}";
        $parse .= "  } ";
        $parse .= "  if ($internalCid) {";
        $parse .= "    \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "    \$cids = \$childs['ids'];";
        $parse .= "    array_push(\$cids, $internalCid);";
        $parse .= "    $id = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field('id,title,post_time,author')->order('id desc')->find();";
        $parse .= "  } else {";
        $parse .= "    \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "    $id = \$ArticleModel->where(\$where)->field('id,title,post_time,author')->order('id desc')->find();";
        $parse .= "  } ";
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
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $pageSize = empty($tag['page-size']) ? 10 : $tag['page-size'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        //$cname = $this->autoBuildVar($cname);dump($cname);die('dd');
        $pageSize = $this->autoBuildVar($pageSize);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  \$page = input('page/d', 1); ";
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalList = [];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];} else { $internalCid = -1;}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'index_category_' . $internalCid . '_' . $pageSize . '_' . \$page;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$targetFields = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    if ($internalCid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $internalCid);";
        $parse .= "      $internalList = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$targetFields)->order('is_top desc,sort,post_time desc')->paginate($pageSize,false,['query'=>input('param.')]);";
        $parse .= "    } else { ";
        $parse .= "      \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "      $internalList = \$ArticleModel->where(\$where)->field(\$targetFields)->order('is_top desc,sort,post_time desc')->paginate($pageSize,false,['query'=>input('param.')]);";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  $assign = $internalList;";
        $parse .= "  ?> ";
        $parse .= "  {volist name='$internalList' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 关键词搜索
     * {article:search keyword='' page-size="10" id=''}{/article:search}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagSearch($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $keyword = empty($tag['keyword']) ? '' : $tag['keyword'];
        $pageSize = empty($tag['page-size']) ? 10 : $tag['page-size'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];
        $id = empty($tag['id']) ? '_id' : $tag['id']; //当做常量使用

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        $keyword = $this->autoBuildVar($keyword);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);

        $parse  = '<?php ';
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalList = [];";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";

        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  \$field = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';";
        $parse .= "  if ($internalCid) { ";
        $parse .= "    \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "    \$cids = \$childs['ids'];";
        $parse .= "    array_push(\$cids, $internalCid);";
        $parse .= "    $internalList = \$ArticleModel->has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->whereLike('title','%'.$keyword.'%','and')->field(\$field)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);";
        $parse .= "  } else { ";
        $parse .= "    $internalList = \$ArticleModel->where(\$where)->whereLike('title','%'.$keyword.'%','and')->field(\$field)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);";
        $parse .= "  };";

        $parse .= "  $assign = $internalList;";
        $parse .= "  ?>";
        $parse .= "  {volist name='$internalList' id='$id'}";
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
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        $cache = $this->autoBuildVar($cache);
        $limit = $this->autoBuildVar($limit);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = '<?php ';
        $parse .= "  $internalCid = $cid;";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalList = [];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'article_hot_list_' . $internalCid . $cache . $limit;";
        $parse .= '  $where = [];';
        $parse .= '  $where[] = [\'status\', \'=\', \app\common\model\ArticleModel::STATUS_PUBLISHED];';
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  \$field = 'id,title,description,author,post_time,read_count,thumb_image_id';";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    if ($internalCid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $internalCid);";
        $parse .= "      $internalList = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$field)->order('read_count desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $internalList = \$ArticleModel->where(\$where)->field(\$field)->order('read_count desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?> ";
        $parse .= "  {volist name='$internalList' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 最新文章
     * {article:latestlist cid='' cache='true' limit='10' id='vo'} {/article:latestlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagLatestlist($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  $internalCid = $cid;";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalList = [];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'article_latest_list_' . $internalCid . $cache . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  \$field = 'id,title,description,author,post_time,read_count,thumb_image_id';";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    if ($internalCid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $internalCid);";
        $parse .= "      $internalList = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$field)->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $internalList = \$ArticleModel->where(\$where)->field(\$field)->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?>";
        $parse .= "  {volist name='$internalList' id='$id' }";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 相关推荐文章列表
     * {article:relatedlist cid='' cache='true' limit='10' id='vo'} {/article:relatedlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagRelatedlist($tag, $content)
    {
        $aid = empty($tag['aid']) ? 0 : $tag['aid'];
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式(为了处理标签传入时是表达式的情况）；
        $aid = $this->autoBuildVar($aid);
        $cid = $this->autoBuildVar($cid);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);
        $internalAid = '$_aid_' . $this->_randVarName(10);

        //需要与外部交互或内嵌标签交互的变量，都不加\$；
        $parse  = "<?php ";
        $parse .= "  $internalCid = $cid;";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];}";
        $parse .= "  }";
        $parse .= "  $internalAid = $aid;";
        $parse .= "  $internalList = [];";
        $parse .= "  \$cacheMark = 'article_latest_list_' . $internalAid . $cache . $limit;";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    \$where = [];";
        $parse .= "    \$where[] = ['article_a_id', '=', $internalAid];";
        $parse .= "    \$whereOr[] = ['article_b_id', '=', $internalAid];";
        $parse .= "    \$field = 'id,article_a_id,article_b_id,title_similar,content_similar';";
        $parse .= "    \$ArticleDataModel = new \app\common\model\ArticleDataModel();";
        $parse .= "    \$dataList = \$ArticleDataModel->where(\$where)->whereOr(\$whereOr)->field(\$field)->order('title_similar desc,content_similar desc')->limit(100)->select();";
        $parse .= "    \$ids = [];";
        $parse .= "    foreach (\$dataList as \$articleData) {";
        $parse .= "      if (\$articleData['article_a_id'] == $internalAid) {";
        $parse .= "        \$ids[] = \$articleData['article_b_id'];";
        $parse .= "      } else {";
        $parse .= "        \$ids[] = \$articleData['article_a_id'];";
        $parse .= "      }";
        $parse .= "    };";

        $parse .= "    \$where = [];";
        $parse .= "    \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "    \$where[] = ['id', 'in', \$ids];";
        $parse .= "    \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "    \$field = 'id,title,description,author,post_time,read_count,thumb_image_id';";
        $parse .= "    if ($internalCid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $internalCid);";
        $parse .= "      $internalList = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$field)->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $internalList = \$ArticleModel->where(\$where)->field(\$field)->order('post_time desc')->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?>";
        $parse .= "  {volist name='$internalList' id='$id' }";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 随机推荐文章列表
     * {article:randomlist cid='' cache='true' limit='10' id='vo'} {/article:randomlist}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagRandomlist($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache']=='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式(为了处理标签传入时是表达式的情况）；
        $cid = $this->autoBuildVar($cid);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(10);
        $internalCid = '$_cid_' . $this->_randVarName(10);
        $internalCname = '$_cname_' . $this->_randVarName(10);

        $parse  = "<?php ";
        $parse .= "  $internalCid = $cid;";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalList = [];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$category = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$category)) { $internalCid = \$category['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'article_latest_list_' . $internalCid . $cache . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  \$field = 'id,title,description,author,post_time,read_count,thumb_image_id';";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    if ($internalCid) { ";
        $parse .= "      \$childs = \app\common\model\CategoryModel::getChild($internalCid);";
        $parse .= "      \$cids = \$childs['ids'];";
        $parse .= "      array_push(\$cids, $internalCid);";
        $parse .= "      $internalList = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',\$cids]])->where(\$where)->field(\$field)->orderRand()->limit($limit)->select();";
        $parse .= "    } else { ";
        $parse .= "      $internalList = \$ArticleModel->where(\$where)->field(\$field)->orderRand()->limit($limit)->select();";
        $parse .= "    } ";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  ?>";
        $parse .= "  {volist name='$internalList' id='$id' }";
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