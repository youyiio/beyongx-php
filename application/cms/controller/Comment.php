<?php
/**
 * Created by PhpStorm.
 * User: cattong
 * Date: 2018-11-30
 * Time: 14:31
 */

namespace app\cms\controller;

use app\common\model\ArticleModel;
use app\common\model\CommentModel;
use app\common\model\MessageModel;
use app\common\model\UserModel;

/**
 * Class Comment 评论
 * @package app\cms\controller
 */
class Comment extends Base
{
    /**
     * 某文章的评论列表
     * @param int $aid
     * @return \think\response\View
     * @throws \think\Exception
     */
    public function index($aid=0)
    {
        $ArticleModel = new ArticleModel();
        $article = $ArticleModel->find($aid);
        if (empty($article)) {
            $this->error('文章不存在');
        }

        $this->assign('aid', $aid);
        $this->assign('article', $article);

        return $this->fetch('index');
    }

    /**
     * 创建评论
     * @param int $aid
     * @return \think\response\View
     * @throws \think\Exception
     */
    public function create($aid=0)
    {
        if (get_config('article_comment_switch') === 'false'){
            $this->error('评论失败:评论功能已关闭');
        }

        $ArticleModel = new ArticleModel();
        $article = $ArticleModel->field('id,title')->find($aid);
        $this->assign('aid', $aid);
        $this->assign('article', $article);

        if (request()->isPost() || request()->isAjax()) {
            $aid = input('aid/d', 0);
            $content = input('content/s', '');
            $check = $this->validate(input('param.'), 'Comment.create');
            if ($check !== true) {
                $this->error($check);
            }

            $content = remove_xss($content);

            $data = [];
            if (get_config('comment_audit_switch') === 'true'){
                $data['status'] = CommentModel::STATUS_PUBLISHING;
            } else {
                $data['status'] = CommentModel::STATUS_PUBLISHED;
            }

            if (session('uid')) {
                $uid = session('uid');
                $user = UserModel::get($uid);
                $author = $user->nickname;
                $data['uid'] = $uid;
                $data['author'] = $author;
            } else {
                $author = session('visitor');
                $data['author'] = $author;
            }

            $author = input('author', '');
            $authorEmail = input('author_email', '');
            $authorUrl = input('author_url', '');
            if (!empty($author)) {
                $data['author'] = $author;
            }
            if (!empty($authorEmail)) {
                $data['author_email'] = $authorEmail;
            }
            if (!empty($authorUrl)) {
                $data['author_url'] = $authorUrl;
            }

            $data['create_time'] = date_time();
            $data['ip'] = request()->ip(0, true);
            $data['article_id'] = $aid;
            $data['content'] = $content;
            $CommentModel = new CommentModel();
            $result = $CommentModel->save($data);

            if (!$result) {
                $this->error('评论发表失败！');
            } else {
                //增加评论数量;
                $ArticleModel->where('id', $aid)->setInc('comment_count');

                //发送评论消息;
                $msgTitle = '新评论消息';
                $msgContent = $author . '评论了文章 “' . $article['title'] . '”';
                send_message(0, 1, $msgTitle, $msgContent, MessageModel::TYPE_COMMENT);

                $this->success('评论添加成功', url('cms/Article/viewArticle', ['aid'=>$aid]));
            }
        }

        return $this->fetch('create');
    }
}