<?php /*a:4:{s:57:"D:\server\wnmp\wwwroot\Cms\theme\wenews\article\list.html";i:1556245320;s:56:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\base.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\header.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\footer.html";i:1556245320;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title><?php echo get_config('site_name'); ?></title>
    <meta name="keywords" content="<?php echo get_config('keywords'); ?>">
    <meta name="description" content="<?php echo get_config('description'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" type="text/css" href="/static/theme/wenews/css/main.css">
    <link rel="stylesheet" type="text/css" href="/static/layui/css/layui.css">
     
</head>
<body class="micronews">
    <div class="micronews-header-wrap">
    <div class="micronews-header w1000 layui-clear">
        <h1 class="logo">
            <a href="<?php echo url('cms/Index/index'); ?>">
                <img src="/static/theme/wenews/images/LOGO.png" alt="<?php echo get_config('site_name'); ?> logo">
                <span class="layui-hide"><?php echo get_config('site_name'); ?></span>
            </a>
        </h1>
        <p class="nav">
            <a href="<?php echo url('cms/Index/index'); ?>" <?php if(request()->controller() == 'Index' && request()->action() == 'index'): ?>
                class="active" <?php endif; ?>>最新</a>
            <?php   $cacheMark = 'categorys_' . 120 . 0 . 5;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $pvJdG_jTl1 = cache($cacheMark);   }   if (empty($pvJdG_jTl1)) {     $CategoryModel = new \app\common\model\CategoryModel();    $pvJdG_jTl1 = $CategoryModel->where($where)->order('sort asc,id asc')->limit(5)->select();    if (120) {      cache($cacheMark, $pvJdG_jTl1, 120);    }  }   $_ids = $pvJdG_jTl1;  if(is_array($pvJdG_jTl1) || $pvJdG_jTl1 instanceof \think\Collection || $pvJdG_jTl1 instanceof \think\Paginator): $i = 0; $__LIST__ = $pvJdG_jTl1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
            <a href="<?php echo url('cms/Article/articleList', ['cname'=>$vo['title_en']]); ?>"
               <?php if(request()->controller() == 'Article' && request()->action() == 'articlelist' && $vo['id'] == $cid): ?>
                class="active"<?php endif; ?>>
                <?php echo htmlentities($vo['title_cn']); ?></a>
              <?php endforeach; endif; else: echo "" ;endif; ?>
        </p>
        <div class="search-bar">
            <form class="layui-form" action="">
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <input type="text" name="title" placeholder="搜索你要的内容" autocomplete="off" class="layui-input">
                        <button class="layui-btn search-btn" formnovalidate><i class="layui-icon layui-icon-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="login">
            <a href="#">
                登录
            </a>
            <!-- <a href="login.html"> -->
            <!-- <img src="/static/theme/wenews/images/header.png" style="width: 36px; height: 36px;"> -->
            <!-- </a> -->
        </div>
        <div class="menu-icon">
            <i class="layui-icon layui-icon-more-vertical"></i>
        </div>
        <div class="mobile-nav">
            <ul class="layui-nav" lay-filter="">
                <li class="layui-nav-item <?php if(request()->controller() == 'Index' && request()->action() == 'index'): ?>layui-this<?php endif; ?>">
                    <a href="<?php echo url('cms/Index/index'); ?>" >最新</a>
                </li>
                <?php   $cacheMark = 'categorys_' . 120 . 0 . 8;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $s_tkmpTeep = cache($cacheMark);   }   if (empty($s_tkmpTeep)) {     $CategoryModel = new \app\common\model\CategoryModel();    $s_tkmpTeep = $CategoryModel->where($where)->order('sort asc,id asc')->limit(8)->select();    if (120) {      cache($cacheMark, $s_tkmpTeep, 120);    }  }   $_ids = $s_tkmpTeep;  if(is_array($s_tkmpTeep) || $s_tkmpTeep instanceof \think\Collection || $s_tkmpTeep instanceof \think\Paginator): $i = 0; $__LIST__ = $s_tkmpTeep;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
                <li class="layui-nav-item <?php if(request()->controller() == 'Article' && request()->action() == 'articlelist' && $vo['id'] == $cid): ?>layui-this<?php endif; ?>">
                    <a href="<?php echo url('cms/Article/articleList', ['cid'=>$vo['id']]); ?>"><?php echo htmlentities($vo['title_cn']); ?></a>
                </li>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div>






    

<div class="micronews-container w1000">
    <div class="layui-fluid">
        <div class="layui-row">
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md8">
                <div class="main">
                    <div class="list-item" id="LAY_demo2">
                        <?php   $page = input('page/d', 1);   $cacheMark = 'index_category_' . $cid . '_' . 10 . '_' . $page;  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $targetFields = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';  if (120) {     $FIKNObA3zY = cache($cacheMark);   }   if (empty($FIKNObA3zY)) {     if ($cid) {       $childs = \app\common\model\CategoryModel::getChild($cid);      $cids = $childs['ids'];      array_push($cids, $cid);      $FIKNObA3zY = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field($targetFields)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);    } else {       $ArticleModel = new \app\common\model\ArticleModel();      $FIKNObA3zY = $ArticleModel->where($where)->field($targetFields)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);    }     if (120) {      cache($cacheMark, $FIKNObA3zY, 120);    }  }   $list = $FIKNObA3zY;  if(is_array($FIKNObA3zY) || $FIKNObA3zY instanceof \think\Collection || $FIKNObA3zY instanceof \think\Paginator): $i = 0; $__LIST__ = $FIKNObA3zY;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
                        <div class="item">
                            <a href="<?php echo url('cms/Article/viewArticle',['aid' => $art['id']]); ?>">
                                <?php if(empty($art['thumb_image_id']) || (($art['thumb_image_id'] instanceof \think\Collection || $art['thumb_image_id'] instanceof \think\Paginator ) && $art['thumb_image_id']->isEmpty())): ?>
                                <img src="">
                                <?php else: ?>
                                <img src="<?php echo htmlentities($art['thumbImage']['full_thumb_image_url']); ?>">
                                <?php endif; ?>
                            </a>
                            <div class="item-info">
                                <h4><a href="<?php echo url('cms/Article/viewArticle',['aid' => $art['id']]); ?>"><?php echo htmlentities($art['title']); ?></a></h4>
                                <div class="b-txt">
                                    <?php if(is_array($art['categorys']) || $art['categorys'] instanceof \think\Collection || $art['categorys'] instanceof \think\Paginator): $i = 0; $__LIST__ = $art['categorys'];if( count($__LIST__)==0 ) : echo "未分类" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <span class="label"><?php echo htmlentities($vo['title_cn']); ?></span>
                                    <?php endforeach; endif; else: echo "未分类" ;endif; ?>
                                    <span class="icon message">
                      <i class="layui-icon layui-icon-dialogue"></i>
                      <?php echo htmlentities($art['comment_count']); ?>
                    </span>
                                    <span class="icon time">
                      <i class="layui-icon layui-icon-log"></i>
                      <?php echo htmlentities(date('Y-m-d H:i',!is_numeric($art['post_time'])? strtotime($art['post_time']) : $art['post_time'])); ?>
                    </span>
                                </div>
                            </div>
                        </div>
                          <?php endforeach; endif; else: echo "" ;endif; ?>
                        <div style="text-align: center; padding:30px 0">
                            <?php echo $list->render(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md4">
                <div class="popular-info">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            <h3>热门资讯</h3>
                        </div>
                        <div class="layui-card-body">
                            <ul class="list-box">
                                <?php   $cacheMark = 'article_hot_list_' . 0 . 120 . 10;  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $ArticleModel = new \app\common\model\ArticleModel();  if (120) {     $LO_gkF6pzp = cache($cacheMark);   }   if (empty($LO_gkF6pzp)) {     if (0) {       $childs = \app\common\model\CategoryModel::getChild(0);      $cids = $childs['ids'];      array_push($cids, 0);      $LO_gkF6pzp  = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    } else {       $LO_gkF6pzp = $ArticleModel->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    }     if (120) {      cache($cacheMark, $LO_gkF6pzp, 120);    }  }   if(is_array($LO_gkF6pzp) || $LO_gkF6pzp instanceof \think\Collection || $LO_gkF6pzp instanceof \think\Paginator): $i = 0; $__LIST__ = $LO_gkF6pzp;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
                                <li class="list">
                                    <a href="<?php echo url('cms/Article/viewArticle',['aid' => $art['id']]); ?>"><?php echo htmlentities($art['title']); ?></a><i class="heat-icon"></i>
                                </li>
                                  <?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- content-laytpl -->
<script id="demo" type="text/html">
    {{#  layui.each(d.itemCont, function(index, item){ }}
    <div class="item">
        {{# if(item.img){ }}
        <a href="details.html">
            <img src="{{item.img}}">
        </a>
        {{# } }}
        <div class="item-info">
            <h4><a href="details.html">{{item.title}}</a></h4>
            <div class="b-txt">
                <span class="label">{{item.label}}</span>
                <span class="icon message">
            <i class="layui-icon layui-icon-dialogue"></i>
            {{item.message}}
          </span>
                <span class="icon time">
            <i class="layui-icon layui-icon-log"></i>
            {{item.time}}
          </span>
            </div>
        </div>
    </div>
    {{#  }); }}
</script>



    <div class="micronews-footer-wrap">
    <div class="micronews-footer w1000">
        <div class="ft-nav">
            <a href="<?php echo url('cms/Index/about'); ?>">关于我们</a>
            <a href="<?php echo url('cms/Index/about'); ?>">合作伙伴</a>
            <a href="#">广告服务</a>
            <a href="#">常见问题</a>
        </div>
        <div class="ft-nav">
            <span>友情链接：</span>
            <?php   $cacheMark = 'links_' . 300 . 4;  if (300) {     $D_x1UxEgEx = cache($cacheMark);   }   if (empty($D_x1UxEgEx)) {     $LinksModel = new \app\common\model\LinksModel();    $D_x1UxEgEx = $LinksModel->field('id,title,url')->order('sort asc')->limit(4)->select();    if (300) {       cache($cacheMark, $D_x1UxEgEx, 300);     }   }   if(is_array($D_x1UxEgEx) || $D_x1UxEgEx instanceof \think\Collection || $D_x1UxEgEx instanceof \think\Paginator): $i = 0; $__LIST__ = $D_x1UxEgEx;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo htmlentities($vo['url']); ?>" target="_blank"><?php echo htmlentities($vo['title']); ?></a>  <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="Copyright">
            <span>&nbsp;</span>&nbsp;&copy;<span><?php echo get_config('site_name'); ?>&nbsp;&nbsp;</span><span><?php echo get_config('icp'); ?></span>
        </div>

        <div class="f-icon">
            <a href="#" class="w-icon">
                <img src="/static/theme/wenews/images/wechat_ic.png">
            </a>
            <a href="#" class="wb-icon">
                <img src="/static/theme/wenews/images/qq_ic.png">
            </a>
        </div>
    </div>
</div>

    <?php echo get_config('stat_code'); ?>

    <script type="text/javascript" src="/static/layui/layui.js"></script>
    <script>
        layui.config({
            base: '/static/theme/wenews/js/'
        }).use('index',function(){
            var index = layui.index;
            index.banner()
            index.seachBtn()
            index.arrowutil()
        });
    </script>

    <script>
        layui.config({
            base: '/static/theme/wenews/js/'
        }).use('index',function(){
            var index = layui.index;
            index.Page('micronews-search-test',80)
            index.omitted('.item p',150)
            index.seachBtn()
            index.arrowutil()
        });
    </script>
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    

</body>
</html>