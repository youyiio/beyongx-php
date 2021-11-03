<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-02-09
 * Time: 11:11
 */

namespace app\common\logic;

use app\common\exception\ModelException;
use app\common\model\BaseModel;
use app\common\model\cms\ArticleMetaModel;
use app\common\model\cms\ArticleModel;
use think\Model;

class ArticleLogic extends Model
{

    public function getHotList($pageIndex=1,$pageSize=10)
    {
        $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();
        return $list;
    }

    public function getRecommendList($keyword,$pageIndex=1, $pageSize=10)
    {
        $keywords = explode(',', $keyword);
        $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
        $where[] = ['keywords', 'like', $keywords];
        $ArticleModel = new ArticleModel();
        $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();

        if (count($list) == 0) {
            unset($where);
            $where[] = ['status', '=', ArticleModel::STATUS_PUBLISHED];
            $list = $ArticleModel->where($where)->field('id,title,post_time')->order('read_count desc')->page($pageIndex, $pageSize)->select();
        }

        return $list;
    }

    //新增文章
    public function addArticle($data = [])
    {
    
        $validator = new \app\api\validate\Article();
        $check = $validator->scene('create')->check($data);
        if ($check !== true) {
            throw new ModelException(0, $validator->getError());
        }

        if ($data['status'] == ArticleModel::STATUS_PUBLISHING || $data['status'] == ArticleModel::STATUS_PUBLISHED) {
            $data['post_time'] = date_time();
        }

        $ArticleModel = new ArticleModel();
      
        $res = $ArticleModel->allowField(true)->isUpdate(false)->save($data);
        if (!$res) {
            return false;
        }
        $artId = $ArticleModel->id;

        //分类，新增中间表数据
        $ArticleModel->categorys()->saveAll($data['category_ids']);

        //标签，添加至meta表
        if (!empty($data['tags'])) {
            foreach($data['tags'] as $tag) {
                if (empty($tag)) {
                    continue;
                }

                $ArticleModel->meta(ArticleMetaModel::KEY_TAG, $tag, BaseModel::MODE_MULTIPLE_VALUE);
            }
        }

        //附加图片，添加至meta表
        if (!empty($data['meta_image_ids'])) {
            foreach($data['meta_image_ids'] as $imageId) {
                if (empty($imageId)) {
                    continue;
                }

                $ArticleModel->meta(ArticleMetaModel::KEY_IMAGE, $imageId, BaseModel::MODE_MULTIPLE_VALUE);
            }
        }

        //附加图片，添加至meta表
        if (!empty($data['meta_file_ids'])) {
            foreach($data['meta_file_ids'] as $fileId) {
                if (empty($fileId)) {
                    continue;
                }

                $ArticleModel->meta(ArticleMetaModel::KEY_FILE, $fileId, BaseModel::MODE_MULTIPLE_VALUE);
            }
        }

        return $artId;
    }

    //修改文章
    public function editArticle($data = [])
    {
        $art = ArticleModel::get(['id'=>$data['id']]);
        
        if (empty($art)) {
            throw new ModelException(0, '文章不存在');
        }

        if ($art->status == ArticleModel::STATUS_DRAFT && $data['status'] == ArticleModel::STATUS_PUBLISHED) {
            //审核开关关闭时
            if (get_config('article_audit_switch') === 'true' ) {
                $data['status'] = ArticleModel::STATUS_PUBLISHING;
            }
            if (empty($art->post_time)) {
                $data['post_time'] = date_time();//设置发布时间
            }
        }

        $validate = new \app\api\validate\Article();
        $check = $validate->scene('edit')->check($data);
        if ($check !== true) {
            throw new ModelException(0, $validate->getError());
            return false;
        }

        $ArticleModel = new ArticleModel();
        $res = $ArticleModel->allowField(true)->isUpdate(true)->save($data);

        // 删除中间表数据
        if (!empty($data['category_ids'])) {
            $art->categorys()->detach();
            $art->categorys()->saveAll($data['category_ids']);
        }

        //标签，添加至meta表
        $ArticleModel->meta(ArticleMetaModel::KEY_TAG, null, BaseModel::MODE_MULTIPLE_VALUE);
        if (!empty($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                if (!empty($tag)) {
                    $ArticleModel->meta(ArticleMetaModel::KEY_TAG, $tag, BaseModel::MODE_MULTIPLE_VALUE);
                }
            }
        }

        //附加图片，添加至meta表
        $ArticleModel->meta(ArticleMetaModel::KEY_IMAGE, null, BaseModel::MODE_MULTIPLE_VALUE);
        if (!empty($data['meta_image_ids'])) {
            foreach ($data['meta_image_ids'] as $imageId) {
                if (!empty($imageId)) {
                    $ArticleModel->meta(ArticleMetaModel::KEY_IMAGE, $imageId, BaseModel::MODE_MULTIPLE_VALUE);
                }
            }
        }

        //附加文件，添加至meta表
        $ArticleModel->meta(ArticleMetaModel::KEY_FILE, null, BaseModel::MODE_MULTIPLE_VALUE);
        if (!empty($data['meta_file_ids'])) {
            foreach ($data['meta_file_ids'] as $fileId) {
                if (!empty($fileId)) {
                    $ArticleModel->meta(ArticleMetaModel::KEY_FILE, $fileId, BaseModel::MODE_MULTIPLE_VALUE);
                }
            }
        }

        return true;
    }
}