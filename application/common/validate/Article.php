<?php
namespace app\common\validate;

use think\Validate;

/**
* 文章模型验证器
*/
class Article extends Validate
{
    //验证规则
    protected $rule = [
        'category_id' => ['require'],
        'category_ids' => ['require'],
        'title' => ['require','max:255'],
        'keywords' => ['max:128'],
        'description' => ['max:255'],
        'content' => ['require'],
        'thumb_image_id' => ["requireIf:category_id,27","requireIf:category_id,26","requireIf:category_id,36"],
        'author' => ["requireIf:category_id,27","requireIf:category_id,26","requireIf:category_id,36"],
        'oldCateIds' => 'require',
    ];

    //错误信息
    protected $message = [
        'id' => '未指定文章',
        'category_id' => '请选择文章分类',
        'category_ids' => '请选择文章分类',
        'title.require' => '请输入文章标题',
        'title.max' => '文章标题超255个字符',
        // 'keywords.require' => '请输入文章关键词',
        'keywords.max' => '文章关键词超128个字符',
        // 'description.require' => '请输入文章摘要',
        'description.max' => '文章摘要超255个字符',
        'content' => '文章内容不能为空',
        'thumb_image_id' => '请上传缩略图',
        'author' => '请输入文章作者',
        'oldCateIds' => '旧的分类不存在',
    ];

    //验证场景
    protected $scene = [
        'add' => ['category_ids','title','keywords','description','content','thumb_image_id','author'],
        'edit' => ['id'=>'require|number','category_ids','title','keywords','description','content','thumb_image_id','author'],
    ];
}
