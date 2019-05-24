<?php /*a:4:{s:64:"D:\server\wnmp\wwwroot\Cms\theme\wenews\article\viewArticle.html";i:1556245320;s:56:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\base.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\header.html";i:1556245320;s:58:"D:\server\wnmp\wwwroot\Cms\theme\wenews\public\footer.html";i:1556245320;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title><?php   $ArticleModel = new \app\common\model\ArticleModel();  $art = $ArticleModel->find(['article_id' => $aid]);  $article = $art;   ?><?php echo htmlentities($article['title']); ?></title>
    <meta name="keywords" content="<?php echo htmlentities($article['keywords']); ?>">
    <meta name="description" content="<?php echo htmlentities($article['description']); ?>">
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
            <?php   $cacheMark = 'categorys_' . 120 . 0 . 5;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $ubW9RDfOEo = cache($cacheMark);   }   if (empty($ubW9RDfOEo)) {     $CategoryModel = new \app\common\model\CategoryModel();    $ubW9RDfOEo = $CategoryModel->where($where)->order('sort asc,id asc')->limit(5)->select();    if (120) {      cache($cacheMark, $ubW9RDfOEo, 120);    }  }   $_ids = $ubW9RDfOEo;  if(is_array($ubW9RDfOEo) || $ubW9RDfOEo instanceof \think\Collection || $ubW9RDfOEo instanceof \think\Paginator): $i = 0; $__LIST__ = $ubW9RDfOEo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
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
                <?php   $cacheMark = 'categorys_' . 120 . 0 . 8;  $where = [];  $where[] = ['status' , '=', \app\common\model\CategoryModel::STATUS_ONLINE];  $where[] = ['pid' , '=', 0];  if (120) {     $WqqE0d0JxN = cache($cacheMark);   }   if (empty($WqqE0d0JxN)) {     $CategoryModel = new \app\common\model\CategoryModel();    $WqqE0d0JxN = $CategoryModel->where($where)->order('sort asc,id asc')->limit(8)->select();    if (120) {      cache($cacheMark, $WqqE0d0JxN, 120);    }  }   $_ids = $WqqE0d0JxN;  if(is_array($WqqE0d0JxN) || $WqqE0d0JxN instanceof \think\Collection || $WqqE0d0JxN instanceof \think\Paginator): $i = 0; $__LIST__ = $WqqE0d0JxN;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?> 
                <li class="layui-nav-item <?php if(request()->controller() == 'Article' && request()->action() == 'articlelist' && $vo['id'] == $cid): ?>layui-this<?php endif; ?>">
                    <a href="<?php echo url('cms/Article/articleList', ['cid'=>$vo['id']]); ?>"><?php echo htmlentities($vo['title_cn']); ?></a>
                </li>
                  <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div>






    

<div class="micronews-container micronews-details-container w1000">
    <div class="layui-fluid">
        <div class="layui-row">
            <div class="layui-col-xs12 layui-col-sm12 layui-col-md8">
                <div class="main">
                    <div class="title">
                        <h3><?php echo htmlentities($article['title']); ?></h3>
                        <div class="b-txt">
                            <?php if(is_array($art['categorys']) || $art['categorys'] instanceof \think\Collection || $art['categorys'] instanceof \think\Paginator): $i = 0; $__LIST__ = $art['categorys'];if( count($__LIST__)==0 ) : echo "未分类" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <span class="label"><?php echo htmlentities($vo['title_cn']); ?></span>
                            <?php endforeach; endif; else: echo "未分类" ;endif; ?>
                            <span class="icon">
                                <i class="layui-icon layui-icon-radio"></i>
                                <b><?php echo htmlentities($article['read_count']); ?></b>人
                            </span>
                            <a href="#message">
                                <span class="icon message">
                                    <i class="layui-icon layui-icon-dialogue"></i>
                                    <b><?php echo htmlentities($art['comment_count']); ?></b>条
                                </span>
                            </a>
                            <span class="icon time">
                                <i class="layui-icon layui-icon-log"></i><?php echo htmlentities($article['create_time']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="article">
                        <article>
                            <?php echo $article['content']; ?>
                        </article>
                        <div class="share-title">
                            <span class="txt">分享:</span>
                            <a href="#">
                                <i class="icon layui-icon layui-icon-login-wechat"></i>
                            </a>
                            <a href="#">
                                <i class="icon layui-icon layui-icon-login-weibo"></i>
                            </a>
                            <a href="#">
                                <i class="icon layui-icon layui-icon-login-qq"></i>
                            </a>
                        </div>

                    <div class="share-title" style="color: #0d8ddb;font-size: 16px" >相关推荐:</div>
                        <div>
                            <ul class="txt">
                                <?php   $cacheMark = 'article_latest_list_' . $aid . 0 . 10;  $where = [];  $where[] = ['article_a_id', '=', $aid];  $whereOr[] = ['article_b_id', '=', $aid];  $dataList = db(CMS_PREFIX . 'article_data')->where($where)->whereOr($whereOr)->field('id,article_a_id,article_b_id,title_similar,content_similar')->order('title_similar desc,content_similar desc')->limit(100)->select();  $ids = [];  foreach ($dataList as $articleData) {    if ($articleData['article_a_id'] == $aid) {      $ids[] = $articleData['article_b_id'];    } else {      $ids[] = $articleData['article_a_id'];    }  };  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $where[] = ['id', 'in', $ids];  $ArticleModel = new \app\common\model\ArticleModel();  if (0) {     $WjZWZMZlio = cache($cacheMark);   }   if (empty($WjZWZMZlio)) {     if (0) {       $childs = \app\common\model\CategoryModel::getChild(0);      $cids = $childs['ids'];      array_push($cids, 0);      $WjZWZMZlio = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit(10)->select();    } else {       $WjZWZMZlio = $ArticleModel->where($where)->field('id,title,description,author,post_time,read_count')->order('post_time desc')->limit(10)->select();    }     if (0) {      cache($cacheMark, $WjZWZMZlio, 0);    }  }   if(is_array($WjZWZMZlio) || $WjZWZMZlio instanceof \think\Collection || $WjZWZMZlio instanceof \think\Paginator): $i = 0; $__LIST__ = $WjZWZMZlio;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <li>
                                    <a href="<?php echo url('cms/Article/viewArticle',['aid' => $vo['id']]); ?>" target="_blank"><?php echo htmlentities($vo['title']); ?></a>
                                </li>
                                  <?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="leave-message" id="message">
                        <div class="tit-box">
                            <span class="tit">网友跟帖</span>
                        </div>
                        <div class="content-box">
                            <div class="tear-box">
                                <a href="#"><img src="/static/theme/wenews/images/header_img1.png"></a>
                                <form class="layui-form" action="<?php echo url('cms/Comment/create'); ?>" method="post">
                                    <div class="layui-form-item layui-form-text">
                                        <div class="layui-input-block">
                                            <textarea id="onInput" name="content" placeholder="请输入内容"
                                                      class="layui-textarea"></textarea>
                                        </div>
                                    </div>
                                    <div class="layui-form-item">
                                        <div class="layui-input-block" style="text-align: right;">
                                            <div class="message-text">
                                                <div class="txt">
                                                </div>
                                            </div>
                                            <div class="layui-form-item">
                                                <div class="layui-input-block" style="text-align: right;">
                                                    <input type="hidden" name="aid" value="<?php echo htmlentities($aid); ?>">
                                                    <button type="submit" class="layui-btn" id="sure">发表</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <?php if($comments): if(is_array($comments) || $comments instanceof \think\Collection || $comments instanceof \think\Paginator): $i = 0; $__LIST__ = $comments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$comment): $mod = ($i % 2 );++$i;?>
                            <div class="ulCommentList">
                                <div class="liCont">
                                    <a href="#"><img src="/static/theme/wenews/images/header_img1.png"></a>
                                    <div class="item-cont">
                                        <div class="cont">
                                            <p><span class="name"><?php echo htmlentities($comment['author']); ?></span><span class="time"><?php echo htmlentities(date("Y-m-d H:i",!is_numeric($comment['create_time'])? strtotime($comment['create_time']) : $comment['create_time'])); ?></span>
                                            </p>
                                            <p class="text"><?php echo htmlentities($comment['content']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            <div style="text-align: center; padding:30px 0">
                                <?php echo $comments->render(); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 留言模版引擎 -->
            <script type="text/html" id="messageTpl">
                <div class="liCont">
                    <a href="">
                        <img src="{{d.avatar}}">
                    </a>
                    <div class="item-cont">
                        <div class="cont">
                            <p><span class="name">{{d.name}}</span><span class="time">1小时前</span></p>
                            <p class="text">{{d.cont}}</p>
                        </div>
                    </div>
                </div>
            </script>

            <div class="layui-col-xs12 layui-col-sm12 layui-col-md4">
                <div class="popular-info">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            <h3>热门资讯</h3>
                        </div>
                        <div class="layui-card-body">
                            <ul class="list-box">
                                <?php   $cacheMark = 'article_hot_list_' . 0 . 120 . 10;  $where = [];  $where[] = ['status', '=', \app\common\model\ArticleModel::STATUS_PUBLISHED];  $ArticleModel = new \app\common\model\ArticleModel();  if (120) {     $gz4Q0lM33i = cache($cacheMark);   }   if (empty($gz4Q0lM33i)) {     if (0) {       $childs = \app\common\model\CategoryModel::getChild(0);      $cids = $childs['ids'];      array_push($cids, 0);      $gz4Q0lM33i  = \app\common\model\ArticleModel::has('CategoryArticle', [['category_id','in',$cids]])->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    } else {       $gz4Q0lM33i = $ArticleModel->where($where)->field('id,title,description,author,post_time,read_count')->order('read_count desc')->limit(10)->select();    }     if (120) {      cache($cacheMark, $gz4Q0lM33i, 120);    }  }   if(is_array($gz4Q0lM33i) || $gz4Q0lM33i instanceof \think\Collection || $gz4Q0lM33i instanceof \think\Paginator): $i = 0; $__LIST__ = $gz4Q0lM33i;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
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


<script type="text/javascript" src="/static/layui/layui.js"></script>
<!--[if lt IE 9]>
<script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
<script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<script>
    layui.config({
        base: '/static/theme/wenews/js/'
    }).use('index', function () {
        var index = layui.index, $ = layui.$;
        index.EnterMessage()
        index.Page('micronews-details-test', 50)
        var collOff = true;
        $('.Collection').on('click', function () {
            if (collOff) {
                $(this).addClass('active')
            } else {
                $(this).removeClass('active')
            }
            collOff = !collOff
        })
        index.seachBtn()
        index.onInput()
        index.arrowutil()
    });
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
            <?php   $cacheMark = 'links_' . 300 . 4;  if (300) {     $j4ygtgcQ28 = cache($cacheMark);   }   if (empty($j4ygtgcQ28)) {     $LinksModel = new \app\common\model\LinksModel();    $j4ygtgcQ28 = $LinksModel->field('id,title,url')->order('sort asc')->limit(4)->select();    if (300) {       cache($cacheMark, $j4ygtgcQ28, 300);     }   }   if(is_array($j4ygtgcQ28) || $j4ygtgcQ28 instanceof \think\Collection || $j4ygtgcQ28 instanceof \think\Paginator): $i = 0; $__LIST__ = $j4ygtgcQ28;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo htmlentities($vo['url']); ?>" target="_blank"><?php echo htmlentities($vo['title']); ?></a>  <?php endforeach; endif; else: echo "" ;endif; ?>
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