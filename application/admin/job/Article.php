<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2019-05-10
 * Time: 17:37
 */

namespace app\admin\job;

use app\common\model\ArticleDataModel;
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
        Log::info('新增文章之后,Queue Job开始...');

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

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章: $articleId 状态未发布");
            $job->delete();
            return;
        }

//        $lcs = new \app\common\library\LCS();
//
//        $ArticleModel = new ArticleModel();
//        $list = $ArticleModel->where([['id', '<', $article->id]])->field('id,title')->select();
//
//        $ArticleDataModel = new ArticleDataModel();
//        foreach ($list as $temp) {
//            $titleSimilar = $lcs->getSimilar($article->title, $temp->title);
//            //小0.4，不存储,减少数据量
//            if ($titleSimilar < 0.4) {
//                continue;
//            }
//
//            $contentSimilar = $titleSimilar;
//            $articleData = [
//                'article_a_id' => $article->id,
//                'article_b_id' => $temp->id,
//                'title_similar' => $titleSimilar,
//                'content_similar' => $contentSimilar,
//                'last_update_time' => date_time(),
//                'create_time' => date_time(),
//            ];
//            $ArticleDataModel->insert($articleData);
//        }

        $this->fullSimilarCompute($article);

        $job->delete();
        Log::info('文章相关性更新已完成');
    }

    public function afterUpdate(Job $job, $data)
    {
        Log::info('更新文章之后,QueueJob开始...');

        $articleId = $data['id'];
        if (empty($articleId)) {
            Log::info("文章articleId: $articleId ");
            $job->delete();
            return;
        }

        $article = ArticleModel::find($articleId);
        if (!$article) {
            Log::info("文章: $articleId 未找到");
            $job->delete();
            return;
        }

        //文章未发布则不做相关度计算
        if ($article['status'] != ArticleModel::STATUS_PUBLISHED) {
            Log::info("文章 状态未发布");
            $job->delete();
            return;
        }

//        $lcs = new \app\common\library\LCS();
//
//        $ArticleDataModel = new ArticleDataModel();
//        $articleBIds = $ArticleDataModel->where('article_a_id', $article->id)->column('id,create_time', 'article_b_id');
//        $articleAIds = $ArticleDataModel->where('article_b_id', $article->id)->column('id,create_time', 'article_a_id');
//        $ids = array_merge(array_keys($articleBIds), array_keys($articleAIds));
//        //Log::debug($articleBIds);
//        //Log::debug($articleAIds);
//        //Log::debug($ids);
//
//        $ArticleModel = new ArticleModel();
//        $list = $ArticleModel->where([['id', 'in', $ids]])->field('id,title')->select();
//        foreach ($list as $temp) {
//            $titleSimilar = $lcs->getSimilar($article->title, $temp->title);
//            $contentSimilar = $titleSimilar;
//            $articleDataId = null;
//            if (array_key_exists($temp->id, $articleBIds)) {
//                $articleDataId = $articleBIds[$temp->id]['id'];
//            } else {
//                $articleDataId = $articleAIds[$temp->id]['id'];
//            }
//            $articleData = [
//                'title_similar' => $titleSimilar,
//                'content_similar' => $contentSimilar,
//                'last_update_time' => date_time(),
//            ];
//            $ArticleDataModel->where('id', $articleDataId)->update($articleData);
//        }

        $this->fullSimilarCompute($article);

        $job->delete();
        Log::info('更新相关性更新已完成');
    }

    //全量相似度计算
    protected function fullSimilarCompute(&$article)
    {
        $lcs = new \app\common\library\LCS();

        $ArticleModel = new ArticleModel();
        $ArticleDataModel = new ArticleDataModel();

        //
        $where = [
            ['id', '<>', $article->id],
            ['status', '=', ArticleModel::STATUS_PUBLISHED],
        ];
        $list = $ArticleModel->where($where)->field('id,title')->select();

        $articleDatas = [];
        foreach ($list as $temp) {
            $titleSimilar = $lcs->getSimilar($article->title, $temp->title);
            $contentSimilar = $titleSimilar;

            if ($article->id <= $temp->id) {
                $data = [
                    'article_a_id' => $article->id,
                    'article_b_id' => $temp->id,
                    'title_similar' => $titleSimilar,
                    'content_similar' => $contentSimilar,
                    'last_update_time' => date_time(),
                    'create_time' => date_time(),
                ];
            } else {
                $data = [
                    'article_a_id' => $temp->id,
                    'article_b_id' => $article->id,
                    'title_similar' => $titleSimilar,
                    'content_similar' => $contentSimilar,
                    'last_update_time' => date_time(),
                    'create_time' => date_time(),
                ];
            }

            $this->insertSimilarQueue($articleDatas, $data);
        }

        //删除旧的数据；
        $ArticleDataModel->where(['article_a_id' => $article->id])->whereOr(['article_b_id' => $article->id])->delete();

        $ArticleDataModel->saveAll($articleDatas);
    }

    //作插入判断,只保留前20名,$articleDatas为有序
    protected function insertSimilarQueue(&$articleDatas, $data)
    {
        if (count($articleDatas) == 0) {
            $articleDatas[] = $data;
            return;
        }

        $last = count($articleDatas) - 1;
        if ($data['title_similar'] < $articleDatas[$last]['title_similar'] && count($articleDatas) >= 20) {
            Log::debug("title similar is too little,pass");
            return;
        }

        for ($i = $last; $i >= 0; $i--) {
            if ($data['title_similar'] <= $articleDatas[$i]['title_similar']) {
                break;
            }
        }
        array_splice($articleDatas, $i+1, 0, [$data]);

//        Log::info($articleDatas);
        if (count($articleDatas) > 20) {
            array_splice($articleDatas, 20);
        }

    }
}