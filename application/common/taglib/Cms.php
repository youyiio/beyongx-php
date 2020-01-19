<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-05-25
 * Time: 17:57
 */

namespace app\common\taglib;

use think\template\TagLib;

class Cms extends TagLib
{
    protected $xml  = 'cms';

    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close表示是否需要闭合（false表示不需要，true表示需要， 默认false） alias 标签别名 level 嵌套层次
        'config' => ['attr' => 'key', 'close' => false], //配置值标签
        'categorys'  => ['attr' => 'cache,cid,cname,id,limit,assign', 'close' => true], //分类列表标签，cid|cname有值时，获取二级分类列表
        'category'  => ['attr' => 'cache,cid,cname,assign', 'close' => true], //根据cid|cname,查询分类信息标签
        'links'  => ['attr' => 'cache,limit,id', 'close' => true], //友情链接标签
        'ads'  => ['attr' => 'cache,type,name,limit,id', 'close' => true], //广告链接标签
        'tags'  => ['attr' => 'cache,limit,id,assign', 'close' => true], //标签云
    ];


    /**
     * 获取配置信息
     * {cms:config key="site_name" /}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagConfig($tag, $content)
    {
        $key = empty($tag['key']) ? 0 : $tag['key'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        //$key = $this->autoBuildVar($key);

        $parse  = "<?php ";
        $parse .= "echo get_config('$key');";
        $parse .= " ?>";

        return $parse;
    }

    /**
     * 根据cid|cname,查询分类信息
     * {cms:category cache='true' cname='company'} {/cms:category}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagCategory($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalCid = '$_cid_' . $this->_randVarName(6);
        $internalCname = '$_cname_' . $this->_randVarName(6);
        $internalCategory = '$_category_' . $this->_randVarName(6);

        $parse  = "<?php ";
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalCategory = null;";
        $parse .= "  \$cacheMark = 'category_' . $cache . $internalCid;";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalCategory = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (!empty($internalCname)) {";
        $parse .= "    \$where = ['title_en'=>$internalCname,'status'=>\app\common\model\CategoryModel::STATUS_ONLINE];";
        $parse .= "    $internalCategory = \app\common\model\CategoryModel::where(\$where)->find();";
        $parse .= "    if ($cache && $internalCategory) {";
        $parse .= "      cache(\$cacheMark, $internalCategory, $cache);";
        $parse .= "    }";
        $parse .= "  } else if (!empty($internalCid)) { ";
        $parse .= "    \$where = ['id'=>$internalCid,'status'=>\app\common\model\CategoryModel::STATUS_ONLINE];";
        $parse .= "    \$CategoryModel = new \app\common\model\CategoryModel();";
        $parse .= "    $internalCategory = \$CategoryModel->where(\$where)->find();";
        $parse .= "    if ($cache && $internalCategory) {";
        $parse .= "      cache(\$cacheMark, $internalCategory, $cache);";
        $parse .= "    }";
        $parse .= "  } ";

        $parse .= "  $assign = $internalCategory;";
        $parse .= "  if (!empty($assign)) { ";
        $parse .= "  ?> ";
        $parse .= $content;
        $parse .= "  <?php ";
        $parse .= "  }";
        $parse .= "  ?>";

        return $parse;
    }

    /**
     * 查询文章分类列表,cid|cname有值，获取二级分类
     * {cms:categorys cache='true' id='vo'} {/cms:categorys}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagCategorys($tag, $content)
    {
        $cid = empty($tag['cid']) ? 0 : $tag['cid'];
        $cname = empty($tag['cname']) ? '' : $tag['cname'];
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $limit = empty($tag['limit']) ? 0 : $tag['limit'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $cid = $this->autoBuildVar($cid);
        $limit = $this->autoBuildVar($limit);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(6);
        $internalCid = '$_cid_' . $this->_randVarName(6);
        $internalCname = '$_cname_' . $this->_randVarName(6);

        $parse  = "<?php ";
        $parse .= "  $internalCid = $cid; ";
        $parse .= "  $internalCname = \"$cname\";";
        $parse .= "  $internalList = [];";
        $parse .= "  if (empty($internalCid) && !empty($internalCname)) {";
        $parse .= "    \$internalCategory = \app\common\model\CategoryModel::where(['title_en'=>$internalCname])->find();";
        $parse .= "    if (!empty(\$internalCategory)) { $internalCid = \$internalCategory['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'categorys_' . $cache . $internalCid . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];";
        $parse .= "  \$where[] = ['pid' , '=', $internalCid];";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    \$CategoryModel = new \app\common\model\CategoryModel();";
        $parse .= "    $internalList = \$CategoryModel->where(\$where)->order('sort asc,id asc')->limit($limit)->select();";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";
        $parse .= "  $assign = $internalList;";
        $parse .= "  ?>";

        $parse .= "  {volist name='$internalList' id='$id'} ";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 友情链接标签
     * {cms:links cache="300" limit='10' id='vo'}{/cms:links}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagLinks($tag, $content)
    {
        $defaultCache = 60 * 5;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $parse  = "<?php ";
        $parse .= "  \$cacheMark = 'links_' . $cache . $limit;";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    \$LinksModel = new \app\common\model\LinksModel();";
        $parse .= "    $list = \$LinksModel->field('id,title,url')->order('sort asc')->limit($limit)->select();";
        $parse .= "    if ($cache) { ";
        $parse .= "      cache(\$cacheMark, $list, $cache); ";
        $parse .= "    } ";
        $parse .= "  } ";

        $parse .= '  ?>';
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * {cms:ads cache="" name="" limit="" id="vo"}{/cms:ads}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagAds($tag, $content)
    {
        $defaultCache = 60 * 5;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $type = empty($tag['type']) ? 0 : $tag['type'];
        $name = empty($tag['name']) ? '' : $tag['name'];
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $parse  = "<?php ";
        $parse .= "  \$internalId = $type; ";
        $parse .= "  \$internalName = \"$name\";";
        $parse .= "  if (empty(\$internalId) && !empty(\$internalName)) {";
        $parse .= "    \$internalAdSlot = \app\common\model\AdSlotModel::where(['title_en'=>\$internalName])->find();";
        $parse .= "    if (!empty(\$internalAdSlot)) { \$internalId = \$internalAdSlot['id'];}";
        $parse .= "  }";
        $parse .= "  \$cacheMark = 'ads_' . \$internalId . $cache . $limit;";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    \$adLogic = new \app\common\logic\AdLogic();";
        $parse .= "    $list = \$adLogic->getAdList(\$internalId, $limit);";
        $parse .= "    if ($cache) { ";
        $parse .= "      cache(\$cacheMark, $list, $cache); ";
        $parse .= "    } ";
        $parse .= "  } ";
        $parse .= "  ?>";
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 查询整站的标签名，标签，统计数量
     * {cms:tags cache='true' limit='10' id='vo'} {/cms:tags}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagTags($tag, $content)
    {
        $defaultCache = 10 * 60;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $id = empty($tag['id']) ? '_id' : $tag['id'];
        $limit = empty($tag['limit']) ? 0 : $tag['limit'];
        $assign = empty($tag['assign']) ? $this->_randVarName(10) : $tag['assign'];

        //作用绑定上下文变量，以':'开头调用函数；以'$'解析为值；非'$'开头的字符串中解析为变量名表达式；
        $limit = $this->autoBuildVar($limit);
        $assign = $this->autoBuildVar($assign);

        //标签内局部变量
        $internalList = '$_list_' . $this->_randVarName(6);
        $internalCname = '$_cname_' . $this->_randVarName(6);

        $parse  = "<?php ";
        $parse .= "  $internalList = [];";
        $parse .= "  \$cacheMark = 'tags_' . $cache . $limit;";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = ['meta_key' , '=', \app\common\model\ArticleMetaModel::KEY_TAG];";
        $parse .= "  if ($cache) { ";
        $parse .= "    $internalList = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($internalList)) { ";
        $parse .= "    \$ArticleMetaModel = new \app\common\model\ArticleMetaModel();";
        $parse .= "    \$field = 'meta_key,meta_value,count(meta_value) as article_count';";
        $parse .= "    \$order = ['article_count' => 'desc'];";
        $parse .= "    $internalList = \$ArticleMetaModel->where(\$where)->field(\$field)->order(\$order)->group('meta_key,meta_value')->limit($limit)->select();";
        $parse .= "    if ($cache) {";
        $parse .= "      cache(\$cacheMark, $internalList, $cache);";
        $parse .= "    }";
        $parse .= "  } ";
        $parse .= "  $assign = $internalList;";
        $parse .= "  ?>";

        $parse .= "  {volist name='$internalList' id='$id'} ";
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