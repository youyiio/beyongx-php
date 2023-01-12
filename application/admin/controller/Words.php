<?php

namespace app\admin\controller;

use Fukuball\Jieba\Jieba;//必须
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;//关键词提取

ini_set('memory_limit', '1024M'); //设置PHP运行占用内存，必须

//composer require fukuball/jieba-php
//实例化：第一个参数表示开启测试模式, dict表示读取small词库，如果是繁体需要改成big
//Jieba::init();
Jieba::init(array('mode' => 'test', 'dict' => 'small')); //必须
Finalseg::init();
JiebaAnalyse::init(); //关键词提取

/**
 * 词句分析工具
 * Words class
 */
class Words extends Base
{
    /**
     * 中文分词
     * 注意：必须是 utf-8 字符串
     */
    public function split()
    {
        //默认精确模式
        $seg_list = Jieba::cut("我来到北京清华大学");
        dump($seg_list);
        //全局模式
        $seg_list = Jieba::cut("我来到北京清华大学", true);
        dump($seg_list);
        //搜索引擎模式
        $seg_list = Jieba::cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造");
        dump($seg_list);
    }

    /**
     * 关键词提取
     * 注意：必须是 utf-8 字符串
     */
    public function keywords($content)
    {
        //越小精确度越高|提取的关键词越准|默认20
        $top_k = 10;
        $content = "我来到北京清华大学";
        //关键词提取
        $tags = JiebaAnalyse::extractTags($content, $top_k);
        
        $this->success('ok', null, $tags);
    }

    /**
     * 导入自定义词库并分词
     */
    public function importCikuFenci()
    {
        //导入自定义的词库（一个词语占一行）
        Jieba::loadUserDict("../public/upload/ciku.txt");///重点在这里，导入自定义的词库
        //词库中就会有你导入的词库
        $seg_list = Jieba::cut("结巴中文分词:做最好的中文分词!");
        dump($seg_list);
    }

    /**
     * 导入自定义词库并提取关键词
     */
    public function importCikuTiqu()
    {
        //导入自定义的词库（一个词语占一行）
        Jieba::loadUserDict("../public/upload/ciku.txt");//自定义的词语
        //越小精确度又高|提取的关键词越准|默认20
        $top_k = 10;
        $content = "这是自定义的词库并且提取自定义关键词";
        //定义截断性比重占比分析(有问题，无法获取到自定义关键词)
        JiebaAnalyse::setStopWords('../public/upload/stop_words.txt');
        //关键词提取
        $tags = JiebaAnalyse::extractTags($content, $top_k);
        dump($tags);
    }
}