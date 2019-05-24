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
        'search'  => ['attr' => 'keyword,id', 'close' => true], //文章搜索标签
        'links'  => ['attr' => 'cache,limit,id', 'close' => true], //友情链接标签
        'ads'  => ['attr' => 'cache,type,limit,id', 'close' => true], //广告链接标签
    ];

    /**
     * 关键词搜索
     * <cms:search keyword='' page-size='10' id=''></cms:search>
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagSearch($tag, $content)
    {
        $keyword = empty($tag['keyword']) ? '' : $tag['keyword'];
        $pageSize = empty($tag['page-size']) ? 10 : $tag['page-size'];

        $id = empty($tag['id']) ? '_id' : $tag['id'];

        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $parse  = "<?php ";
        $parse .= "  \$where = [];";
        $parse .= "  \$where[] = [\'status\', \'=\', \app\common\model\ArticleModel::STATUS_PUBLISHED];";
        $parse .= "  \$ArticleModel = new \app\common\model\ArticleModel();";
        $parse .= '$' . $list . " = \$ArticleModel->where(\$where)->whereLike('title','%$keyword%', 'and')->field('id,title,thumb_image_id,description,author,post_time')->order('is_top desc,sort,post_time desc')->paginate($pageSize, false,['query'=>input('param.')]);";
        $parse .= "  ?> ";
        $parse .= "  {volist name='$list' id='$id'}";
        $parse .= $content;
        $parse .= "  {/volist}";

        return $parse;
    }

    /**
     * 友情链接标签
     * <cms:links cache="300" limit='10' id='vo'></cms:links>
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
     * <cms:ads cache="" type="" limit="" id="vo"></cms:ads>
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagAds($tag, $content)
    {
        $defaultCache = 60 * 5;
        $cache = empty($tag['cache']) ? $defaultCache : (strtolower($tag['cache'] =='true')? $defaultCache:intval($tag['cache']));
        $type = empty($tag['type']) ? 10 : $tag['type'];
        $limit = empty($tag['limit']) ? 10 : $tag['limit'];
        $id = empty($tag['id']) ? '_id' : $tag['id'];

        $list = $this->_randVarName(10);
        $list = $this->autoBuildVar($list);

        $parse  = '<?php ';
        $parse .= "  \$cacheMark = 'links_' . $cache . $limit;";
        $parse .= "  if ($cache) { ";
        $parse .= "    $list = cache(\$cacheMark); ";
        $parse .= "  } ";
        $parse .= "  if (empty($list)) { ";
        $parse .= "    \$adLogic = new \app\common\logic\AdLogic();";
        $parse .= "    $list = \$adLogic->getAdList($type, $limit);";
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