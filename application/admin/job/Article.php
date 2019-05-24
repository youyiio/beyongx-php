<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-05-10
 * Time: 17:37
 */

namespace app\admin\job;

use think\queue\Job;
use think\facade\Log;
use think\Db;
use app\common\model\ArticleModel;

class Article
{
    /**
     * 新增文章之后，执行job
     * @param Job $job
     * @param $data
     * @throws \Exception
     */
    public function afterInsert(Job $job, $data)
    {
        Log::info('新增文章之后Job开始...');

        $articleId = $data['id'];
        if (empty($articleId)) {
            $job->delete();
            return;
        }

        $article = ArticleModel::find($articleId);
        if (!$article) {
            Log::info("文章: $articleId 未找到");
            $job->delete();
            return;
        }

        $lcs = new \app\common\library\LCS();

        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where([['id', '<', $article->id]])->field('id,title')->select();
        foreach ($list as $temp) {
            $titleSimilar = $lcs->getSimilar($article->title, $temp->title);
            $contentSimilar = $titleSimilar;
            $articleData = [
                'article_a_id' => $article->id,
                'article_b_id' => $temp->id,
                'title_similar' => $titleSimilar,
                'content_similar' => $contentSimilar,
                'last_update_time' => date_time(),
                'create_time' => date_time(),
            ];
            Db::name(CMS_PREFIX . 'article_data')->insert($articleData);
        }

        $job->delete();
        Log::info('文章相关性更新已完成');
    }

    public function afterUpdate(Job $job, $data)
    {
        Log::info('更新文章之后Job开始...');

        $articleId = $data['id'];
        if (empty($articleId)) {
            $job->delete();
            return;
        }

        $article = ArticleModel::find($articleId);
        if (!$article) {
            Log::info("文章: $articleId 未找到");
            $job->delete();
            return;
        }

        $lcs = new \app\common\library\LCS();

        $articleBIds = Db::name(CMS_PREFIX . 'article_data')->where('article_a_id', $article->id)->column('id,create_time', 'article_b_id');
        $articleAIds = Db::name(CMS_PREFIX . 'article_data')->where('article_b_id', $article->id)->column('id,create_time', 'article_a_id');
        $ids = array_merge(array_keys($articleBIds), array_keys($articleAIds));
        //Log::debug($articleBIds);
        //Log::debug($articleAIds);
        //Log::debug($ids);

        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where([['id', 'in', $ids]])->field('id,title')->select();
        foreach ($list as $temp) {
            $titleSimilar = $lcs->getSimilar($article->title, $temp->title);
            $contentSimilar = $titleSimilar;
            $articleDataId = null;
            if (array_key_exists($temp->id, $articleBIds)) {
                $articleDataId = $articleBIds[$temp->id]['id'];
            } else {
                $articleDataId = $articleAIds[$temp->id]['id'];
            }
            $articleData = [
                'title_similar' => $titleSimilar,
                'content_similar' => $contentSimilar,
                'last_update_time' => date_time(),
            ];
            Db::name(CMS_PREFIX . 'article_data')->where('id', $articleDataId)->update($articleData);
        }

        $job->delete();
        Log::info('更新相关性更新已完成');
    }
}