<?php /*a:4:{s:56:"D:\server\wnmp\wwwroot\Cms\theme\wenews\index\index.html";i:1557106232;s:56:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\base.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\header.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\footer.html";i:1556245320;}*/ ?>
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
            <?php   $cacheMark = 'categorys_' . 120 . 0 . 5;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $jIdXd3QYr2 = cache($cacheMark);   }   if (empty($jIdXd3QYr2)) {     $CategoryModel = new \app\common\model\CategoryModel();    $jIdXd3QYr2 = $CategoryModel->where($where)->order('sort asc,id asc')->limit(5)->select();    if (120) {      cache($cacheMark, $jIdXd3QYr2, 120);    }  }   $_ids = $jIdXd3QYr2;  if(is_array($jIdXd3QYr2) || $jIdXd3QYr2 instanceof \think\Collection || $jIdXd3QYr2 instanceof \think\Paginator): $i = 0; $__LIST__ = $jIdXd3QYr2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
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
                <?php   $cacheMark = 'categorys_' . 120 . 0 . 8;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $l5yEv_2y0_ = cache($cacheMark);   }   if (empty($l5yEv_2y0_)) {     $CategoryModel = new \app\common\model\CategoryModel();    $l5yEv_2y0_ = $CategoryModel->where($where)->order('sort asc,id asc')->limit(8)->select();    if (120) {      cache($cacheMark, $l5yEv_2y0_, 120);    }  }   $_ids = $l5yEv_2y0_;  if(is_array($l5yEv_2y0_) || $l5yEv_2y0_ instanceof \think\Collection || $l5yEv_2y0_ instanceof \think\Paginator): $i = 0; $__LIST__ = $l5yEv_2y0_;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
                <li class="layui-nav-item <?php if(request()->controller() == 'Article' && request()->action() == 'articlelist' && $vo['id'] == $cid): ?>layui-this<?php endif; ?>">
                    <a href="<?php echo url('cms/Article/articleList', ['cid'=>$vo['id']]); ?>"><?php echo htmlentities($vo['title_cn']); ?></a>
                </li>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div>






    

<div class="layui-fulid micronews-banner w1000">
  <div class="layui-carousel imgbox" id="micronews-carouse">
    <div carousel-item>
      <?php   $cacheMark = 'links_' . 120 . 5;  if (120) {     $z6HgyUvpT_ = cache($cacheMark);   }   if (empty($z6HgyUvpT_)) {     $adLogic = new \app\common\logic\AdLogic();    $z6HgyUvpT_ = $adLogic->getAdList(1, 5);    if (120) {       cache($cacheMark, $z6HgyUvpT_, 120);     }   }   if(is_array($z6HgyUvpT_) || $z6HgyUvpT_ instanceof \think\Collection || $z6HgyUvpT_ instanceof \think\Paginator): $i = 0; $__LIST__ = $z6HgyUvpT_;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
      <div>
        <p class="title" style="color: black"><?php echo htmlentities($vo['title']); ?></p>
        <a href="<?php echo htmlentities($vo['url']); ?>"><img src="<?php echo htmlentities($vo['image']['full_thumb_image_url']); ?>"></a>
      </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
  </div>
</div>

  <div class="micronews-container w1000">
    <div class="layui-fluid">
      <div class="layui-row">
        <div class="layui-col-xs12 layui-col-sm12 layui-col-md8">
          <div class="main">
            <div class="list-item" id="LAY_demo2">
              <?php   $page = input('page/d', 1);   $cacheMark = 'index_category_' . 0 . '_' . 10 . '_' . $page;  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $targetFields = 'id,title,description,author,thumb_image_id,post_time,read_count,comment_count';  if (120) {     $v4uXA7of3e = cache($cacheMark);   }   if (empty($v4uXA7of3e)) {     if (0) {       $childs = \app\common\model\CategoryModel::getChild(0);      $cids = $childs['ids'];      array_push($cids, 0);      $v4uXA7of3e = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field($targetFields)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);    } else {       $ArticleModel = new \app\common\model\ArticleModel();      $v4uXA7of3e = $ArticleModel->where($where)->field($targetFields)->order('is_top desc,sort,post_time desc')->paginate(10,false,['query'=>input('param.')]);    }     if (120) {      cache($cacheMark, $v4uXA7of3e, 120);    }  }   $list = $v4uXA7of3e;  if(is_array($v4uXA7of3e) || $v4uXA7of3e instanceof \think\Collection || $v4uXA7of3e instanceof \think\Paginator): $i = 0; $__LIST__ = $v4uXA7of3e;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
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
                  <?php   $cacheMark = 'article_hot_list_' . 0 . 120 . 10;  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $ArticleModel = new \app\common\model\ArticleModel();  if (120) {     $PKXmOf_Hx_ = cache($cacheMark);   }   if (empty($PKXmOf_Hx_)) {     if (0) {       $childs = \app\common\model\CategoryModel::getChild(0);      $cids = $childs['ids'];      array_push($cids, 0);      $PKXmOf_Hx_  = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    } else {       $PKXmOf_Hx_ = $ArticleModel->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    }     if (120) {      cache($cacheMark, $PKXmOf_Hx_, 120);    }  }   if(is_array($PKXmOf_Hx_) || $PKXmOf_Hx_ instanceof \think\Collection || $PKXmOf_Hx_ instanceof \think\Paginator): $i = 0; $__LIST__ = $PKXmOf_Hx_;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
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
      {{#  } }}
      <div class="item-info">
        <h4><a href="<?php echo url('cms/Article/viewArticle',['aid' => 0]); ?>">{{item.title}}</a></h4>
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
  <!-- end-content-laytpl-->


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
            <?php   $cacheMark = 'links_' . 300 . 4;  if (300) {     $zI3jwplS0y = cache($cacheMark);   }   if (empty($zI3jwplS0y)) {     $LinksModel = new \app\common\model\LinksModel();    $zI3jwplS0y = $LinksModel->field('id,title,url')->order('sort asc')->limit(4)->select();    if (300) {       cache($cacheMark, $zI3jwplS0y, 300);     }   }   if(is_array($zI3jwplS0y) || $zI3jwplS0y instanceof \think\Collection || $zI3jwplS0y instanceof \think\Paginator): $i = 0; $__LIST__ = $zI3jwplS0y;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo htmlentities($vo['url']); ?>" target="_blank"><?php echo htmlentities($vo['title']); ?></a>  <?php endforeach; endif; else: echo "" ;endif; ?>
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