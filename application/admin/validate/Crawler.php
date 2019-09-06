<?php
namespace app\admin\validate;

use think\Validate;
class Crawler extends Validate
{
    //验证规则
    protected $rule = [
        'id' => ['integer', 'gt:0'],
        'title' => ['require','max'=> 32], //标题
        'url'   => ['require','url'],
        'encode' => ['integer'],
        'is_timing' => ['boolean'],
        'is_paging' => ['boolean', 'checkIsPaging'],
        'end_page'   => ['integer'],
        'start_page' => ['integer'],
        'paging_url'   => ['require'],
        'article_url' => ['require', 'checkSelectorFormat:文章网址'],
        'article_title' => ['require', 'checkSelectorFormat:文章标题'],
        'article_description' => ['require', 'checkSelectorFormat:文章简介'],
        'article_keywords' => ['require', 'checkSelectorFormat:文章关键字'],
        'article_content' => ['require', 'checkSelectorFormat:文章内容'],
        'article_author' => ['checkSelectorFormat:文章作者'],
        'article_image' => ['checkSelectorFormat:文章图片'],
    ];

    //错误信息
    protected $message = [
        'id' => 'id不能为空',
        'title.require' => '请填写标题',
        'title.max' => '标题最多32个字符',
        'url.require' => '请填写url',
        'url.url' => '请填写正确的url',
        'start_page' => '起始页码请填写数字',
        'end_page'   => '结束页码请填写数字',
        'paging_url'   => '请填写分页网址规则',
        'article_url' => '请填写文章url规则',
        'article_title' => '请填写文章标题规则',
        'article_description' => '请填写文章简介规则',
        'article_keywords' => '请填写文章关键字规则',
        'article_content' => '请填写文章内容规则'
    ];

    //检查is_paging为1时，start_page,end_page,paging_url是否有值
    protected function checkIsPaging($value, $rule, $data)
    {
        if ($value !== 1) {
            return true;
        }

        $check = $this->checkRule($data['start_page'], ['integer']);
        if ($check !== true) {
            return '起始页码请填写数字';
        }
        $check = $this->checkRule($data['end_page'], ['integer']);
        if ($check !== true) {
            return '结束页码请填写数字';
        }
        $check = $this->checkRule($data['paging_url'], ['require']);
        if ($check !== true) {
            return '请填写分页网址规则';
        }

        return true;
    }

    //检查选择器格式：$rule来自于定义: checkSelectorFormat:xxx
    protected function checkSelectorFormat($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        $array = explode(',', $value);
        if (empty($array) || count($array) != 2) {
            return $rule . ' 规则:' .$value. ' 格式不正确！正确格式为：selector,attribute';
        }

        return true;
    }

    //验证场景
    protected $scene = [
        'add' => ['title','url', 'encode', 'is_timing', 'is_paging', 'article_url',
            'article_title', 'article_description', 'article_keywords', 'article_content', 'article_author', 'article_image'], //新增规则
        'edit' => ['id','title','url', 'encode', 'is_timing', 'is_paging', 'article_url',
            'article_title', 'article_description', 'article_keywords', 'article_content', 'article_author', 'article_image'], //修改规则
        'test' => ['title','url', 'encode', 'is_timing', 'is_paging', 'article_url',
            'article_title', 'article_description', 'article_keywords', 'article_content', 'article_author', 'article_image'], //测试采集
    ];
}