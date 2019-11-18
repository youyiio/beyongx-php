<?php
namespace app\common\taglib;


use think\template\TagLib;

class Comment extends TagLib
{
    protected $xml  = 'comment';

    /**
     * 定义标签列表
     */
    protected $tags  =  [
        // 标签定义： attr 属性列表 close表示是否需要闭合（false表示不需要，true表示需要， 默认false） alias 标签别名 level 嵌套层次
        //cache：是否缓冲，值true,false,int(秒); cid：分类id; assign:结果的返回值,变量后续可使用，赋值给相应的变量; id:定义循环或结果的变量；
        'view' => ['attr' => 'aid,id,assign', 'close' => true],  //评论明细信息标签
        'list'   => ['attr' => 'aid,cmtId,cache,page-size,id,assign', 'close' => true], //评论列表标签,cmtid评论id
        'hotlist' => ['attr' => 'aid,cache,limit,id', 'close' => true], //热门评论列表标签
        'latestlist' => ['attr' => 'aid,cache,limit,id', 'close' => true], //最新评论列表标签
    ];
}