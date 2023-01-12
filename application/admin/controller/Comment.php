<?php
namespace app\admin\controller;

use app\common\model\cms\CommentModel;
use app\common\model\MessageModel;
use app\common\model\UserModel;

/**
* 评论控制器
*/
class Comment extends Base
{
 
    //评论列表
    public function index()
    {
        $CommentModel = new CommentModel();

        $map = [];
        $key = input('param.key');
        if (!empty($key)) {
            $map[] = ['content', 'like',"%{$key}%"];
        }

        $startTime = input('param.startTime', '');
        $endTime = input('param.endTime', '');

        if (!empty($endTime)) {
            $map[] = ['create_time', '<=', $endTime . ' 23:59:59'];
        }
        if (!empty($startTime)) {
            $map[] = ['create_time', '>=', $startTime . ' 00:00:00'];
        }

        $fields = 'id, content, article_id, create_time, status, author, ip, pid';
        $list = $CommentModel->where($map)->field($fields)->order('create_time desc')->distinct('id')->paginate(20, false);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        $MessageModel = new MessageModel();
        $data['status'] = MessageModel::STATUS_READ;
        $data['read_time'] = date_time();
        $data['is_readed'] = 1;//0未读，1已读
        $MessageModel->save($data, ['type' => MessageModel::TYPE_COMMENT]);

        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);


        return $this->fetch('comment/index');
    }

    //审核评论
    public function auditComment($id=0, $pass=1)
    {
        $com = CommentModel::get(['id'=>$id]);
        if (empty($com)) {
            $this->error('评论不存在');
        }

        if ($com->status != CommentModel::STATUS_PUBLISHING) {
            $this->error('评论审核未通过，无法发布');
        }

        if ($pass) {
            $com->status = CommentModel::STATUS_PUBLISHED;
        } else {
            $com->status = CommentModel::STATUS_REFUSE;
        }

        $res = $com->save();
        if ($res !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }

    }

    //回复评论
    public function postComment()
    {
        if (request()->isAjax()) {
            $aid = input('article_id/d', 0);
            $pid = input('pid/d', 0);
            $content = input('content/s', '');

            $data = [];
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

            $data['create_time'] = date_time();
            $data['status'] = CommentModel::STATUS_PUBLISHED;
            $data['ip'] = request()->ip(0, true);
            $data['article_id'] = $aid;
            $data['content'] = remove_xss($content);
            $data['pid'] = $pid;
            $CommentModel = new CommentModel();
            $result = $CommentModel->save($data);
            if (!$result) {
                $this->error('回复失败');
            } elseif (stripos($_SERVER["HTTP_REFERER"], 'viewComments')) {
                $this->success('回复成功', url("Comment/viewComments", ['id' => $pid]));
            } else {
                $this->success('回复成功', url('Comment/index'));
            }
        }

         return $this->fetch('comment/index');
    }

    //删除评论
    public function deleteComment($id)
    {
        $ids = explode(',', $id);
        $CommentModel = new CommentModel();

        $numRows = $CommentModel->where([['id', 'in', $ids]])->delete();
        if ($numRows  == count($ids)) {
            $this->success('成功删除');
        } else {
            $fails = count($ids) - $numRows;
            $this->error("成功删除 $numRows 条，失败 $fails 条!");
        }
    }

    //查看评论下的回复
    public  function viewComments($id)
    {
        $comment = CommentModel::get(['id'=>$id]);

        if (empty($comment)) {
            $this->error('评论不存在');
        }

        $CommentModel = new CommentModel();
        $where = [
            'pid' => $comment['id'],
            'status' => CommentModel::STATUS_PUBLISHED
        ];
        $pageConfig = [
            'type' => '\\app\\common\\paginator\\BootstrapTable',
        ];
        $comments = $CommentModel->where($where)->order('id desc')->paginate(6, false, $pageConfig);

        $this->assign('comments', $comments);
        $this->assign('comment', $comment);

        return $this->fetch('comment/viewComments');
    }

}
