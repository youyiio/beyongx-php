<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-01-14
 * Time: 14:09
 */

namespace app\common\validate;

use think\Validate;

/**
 * 文章模型验证器
 */
class Comment extends Validate
{
//验证规则
    protected $rule = [
        'content' => ['require', 'max:255'],
        'author' => ['require', 'max:24'],
        'author_email' => ['require'],
        'author_url' => ['url'],
        'article_id' => ['require'],
        'aid' => ['require', 'integer'],
    ];

    //错误信息
    protected $message = [
        'content' => '评论内容不能为空',
        'author' => '请填写昵称',
        'author_email' => '请填写邮箱',
        'author_url' => '请填写url',
        'article_id' => '文章id格式不正确',
        'aid' => '文章id格式不正确',
    ];

    //验证场景
    protected $scene = [
        'create' => ['content','aid'],
    ];
}