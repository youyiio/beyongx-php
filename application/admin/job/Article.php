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

use app\common\model\ArticleModel;
use app\common\model\ArticleDataModel;
use app\common\model\ArticleMetaModel;

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

        self::fullSimilarCompute($articleId);

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

        self::fullSimilarCompute($articleId);

        $job->delete();
        Log::info('更新相关性更新已完成');
    }

    public function timingPost(Job $job, $data)
    {
        Log::info('定时发布文章 job, start...');

        $result = self::postTimingArticles();
        $successCount = $result['success_count'];
        $failCount = $result['fail_count'];

        Log::info("定时发布文章 stat:  ($successCount) success, (" . ($failCount) .") error!");
        Log::info('定时发布文章 job end...');
    }

    //*****************静态业务逻辑，供Job及command调用**********************
    /**
     * 发布定时文章
     * @return array, ['success_count','fail_count']
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function postTimingArticles()
    {
        $currentTime = date_time();
        $where = [
            ['meta_key', '=', ArticleMetaModel::KEY_TIMING_POST],
            ['meta_value', '<=', $currentTime]
        ];

        $ArticleMetaModel = new ArticleMetaModel();
        $metas = $ArticleMetaModel->where($where)->select();
        //print_r($metas);

        $totalCount = count($metas);
        $successCount = 0;
        foreach ($metas as $meta) {
            $articleId = $meta->article_id;

            $ArticleModel = ArticleModel::get($articleId);
            if ($ArticleModel['status'] == ArticleModel::STATUS_PUBLISHED
                || $ArticleModel['status'] == ArticleModel::STATUS_DELETED) {
                ArticleMetaModel::destroy(['id' => $meta->id]);
                continue;
            }

            $data = [
                'status' => ArticleModel::STATUS_PUBLISHED,
                'post_time' => $meta->meta_value,
            ];

            $result = $ArticleModel->isUpdate(true)->save($data, ['id' => $articleId]);
            if ($result) {
                $successCount++;
            }

            ArticleMetaModel::destroy(['id' => $meta->id]);
        }

        return ['success_count' => $successCount, 'fail_count' => $totalCount - $successCount];
    }

    /**
     * 区间发布文章
     * @param $startId
     * @param $endId
     * @return array, ['success_count','fail_count']
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function postRangeArticles($startId, $endId)
    {

        $where = [
            ['id', '>=', $startId],
            ['id', '<=', $endId],
            ['status', '<>', ArticleModel::STATUS_PUBLISHED],
            ['status', '<>', ArticleModel::STATUS_DELETED],
        ];

        $ids = ArticleModel::where($where)->column('id');

        $totalCount = count($ids);
        $successCount = 0;
        foreach ($ids as $id) {
            $ArticleModel = ArticleModel::get($id);

            $data = [
                'status' => ArticleModel::STATUS_PUBLISHED,
                'post_time' => date_time(),
            ];

            $result = $ArticleModel->isUpdate(true)->save($data, ['id' => $id]);
            if ($result) {
                $successCount++;
            }

            //如果文章已定时，去除定时设定
            ArticleMetaModel::destroy(['meta_key' => ArticleMetaModel::KEY_TIMING_POST, 'target_id' => $id]);
        }

        return ['success_count' => $successCount, 'fail_count' => $totalCount - $successCount];
    }

    //全量相似度计算
    public static function fullSimilarCompute($articleId)
    {
        $article = ArticleModel::get($articleId);
        if (!$article) {
            return false;
        }

        $lcs = new \app\common\library\LCS();

        $ArticleModel = new ArticleModel();
        $ArticleDataModel = new ArticleDataModel();

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
                    'update_time' => date_time(),
                    'create_time' => date_time(),
                ];
            } else {
                $data = [
                    'article_a_id' => $temp->id,
                    'article_b_id' => $article->id,
                    'title_similar' => $titleSimilar,
                    'content_similar' => $contentSimilar,
                    'update_time' => date_time(),
                    'create_time' => date_time(),
                ];
            }

            self::insertSimilarQueue($articleDatas, $data);
        }

        //删除旧的数据；
        $ArticleDataModel->where(['article_a_id' => $article->id])->whereOr(['article_b_id' => $article->id])->delete();

        $ArticleDataModel->saveAll($articleDatas);
    }

    //作插入判断,只保留前20名,$articleDatas为有序
    protected static function insertSimilarQueue(&$articleDatas, $data)
    {
        if (count($articleDatas) == 0) {
            $articleDatas[] = $data;
            return;
        }

        $last = count($articleDatas) - 1;
        if ($data['title_similar'] < $articleDatas[$last]['title_similar'] && count($articleDatas) >= 20) {
            //Log::debug("title similar is too little,pass");
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