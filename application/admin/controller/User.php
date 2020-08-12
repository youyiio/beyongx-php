<?php
namespace app\admin\controller;

use app\common\logic\PushLogic;
use app\common\model\ActionLogModel;
use app\common\model\ArticleModel;
use app\common\model\AuthGroupAccessModel;
use app\common\model\AuthGroupModel;
use app\common\model\Message;
use app\common\model\UserModel;
use think\facade\Cache;


/**
* 用户管理控制器
*/
class User extends Base
{
    //用户列表
    public function index()
    {
        $map['status'] = ['egt',0];
        $type = input('param.type');
        $key = input('param.key');
        $map = [];
        if ($key && $type) {
            switch ($type) {
                case 'mobile':
                    $key = trim($key);
                    $map[] = ['mobile','like',"%$key%"];
                    break;
                case 'email':
                    $key = trim($key);
                    $map[] = ['email','like',"%$key%"];
                    break;
                case 'uid':
                    $key = intval($key);
                    $map['id'] = $key;
                    break;
                default:
                    break;
            }
        }

        $UserModel = new UserModel();
        $list = $UserModel->where($map)->order('id desc')->with('groups')->paginate(10, false, ['query'=>input('param.')]);
        if (request()->param('status')) {
            $status = input('param.status');
            $list = $UserModel->where($map)->where('status',$status)->order('id desc')->with('groups')->paginate(10,false,['query'=>input('param.')]);
        }

        $userTotal = $UserModel->count('id');
        $freezeTotal = $UserModel->where('status','=',UserModel::STATUS_FREEZED)->count('id');
        $activeTotal = $UserModel->where('status','=',UserModel::STATUS_ACTIVED)->count('id');
        $this->assign('userTotal', $userTotal);
        $this->assign('freezeTotal', $freezeTotal);
        $this->assign('activeTotal', $activeTotal);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        return view();
    }

    //新增用户
    public function addUser()
    {
        if (request()->isAjax()) {
            $nickname = input('post.nickname/s');
            $mobile = input('post.mobile/s');
            $email = input('post.email/s');
            $password = input('post.password/s');
            if (!validate('User')->scene('add')->check(input('post.'))) {
                $this->error(validate('User')->getError());
            }
            $userModel = new UserModel();
            $user = $userModel->createUser($mobile, $password, $nickname, $email);

            if ($user) {
                $data = input('post.');
                if (!empty($data['group_ids'])) {
                    $group = [];
                    foreach ($data['group_ids'] as $k => $v) {
                        $group[] = [
                            'uid' => $user->id,
                            'group_id' => $v
                        ];
                    }
                    $AuthGroupAccessModel = new AuthGroupAccessModel();
                    $AuthGroupAccessModel->insertAll($group);
                }
                $this->success('成功新增用户',url('User/index'));
            } else {
                $this->error($userModel->getError());
            }
        }

        $AuthGroupModel = new AuthGroupModel();
        $groups = $AuthGroupModel->where('status', 1)->field('id,title')->select();
        $this->assign('groups',$groups);

        return $this->fetch('addUser');
    }

    //修改用户
    public function editUser()
    {
        if (request()->isAjax()) {
            $data = input('post.');
            // 组合where数组条件
            $uid = $data['uid'];

            //验证
            $validate = validate('User');
            $check = $validate->scene('edit')->check($data);
            if ($check !== true) {
                $this->error($validate->getError());
            }

            // 修改权限
            $AuthGroupAccessModel = new AuthGroupAccessModel();
            $AuthGroupAccessModel->where(['uid'=>$uid])->delete();
            if (!empty($data['group_ids'])) {
                $group = [];
                foreach ($data['group_ids'] as $k => $v) {
                    $group[] = [
                        'uid'=>$uid,
                        'group_id'=>$v
                    ];
                }
                $AuthGroupAccessModel->insertAll($group);
            }
            Cache::tag('menu')->rm($uid); //删除用户菜单配置缓存

            $userModel = new UserModel();
            $res = $userModel->editUser($uid, $data);

            if ($res !== false) {
                $this->success('成功修改', url('User/index'));
            } else {
                $this->error($userModel->getError());
            }
        }

        $uid = input('param.uid', 0);
        if ($uid == 0) {
            $this->error('参数错误');
        }

        $user = UserModel::get($uid);
        $this->assign('user', $user);

        $AuthGroupAccessModel = new AuthGroupAccessModel();
        $userGroups = $AuthGroupAccessModel->where('uid', $uid)->column('group_id');
        $this->assign('userGroups', $userGroups);

        $AuthGroupModel = new AuthGroupModel();
        $groups = $AuthGroupModel->where('status',1)->field('id,title')->select();
        $this->assign('groups', $groups);

        return $this->fetch('editUser');
    }

    //删除用户
    public function deleteUser()
    {
        $uid = input('uid/d',0);
        if ($uid == 0) {
            $this->error('参数错误');
        }

        $res = UserModel::where('id', $uid)->setField('status', UserModel::STATUS_DELETED);
        if ($res) {
            $this->success('成功删除用户');
        } else {
            $this->success('删除失败');
        }
    }

    //查看用户
    public function viewUser()
    {
        $uid = input('uid/d',0);
        if ($uid == 0) {
            $this->error('参数错误');
        }

        $userModel = new UserModel;
        $user = $userModel::get($uid);
        $this->assign('user',$user);

        //最新文章列表
        $ArticleModel = new ArticleModel();
        $articleList = $ArticleModel->where('uid',$uid)->order('id desc')->paginate(20, false, ['query'=>input('param.')]);
        $this->assign('articleList', $articleList);

        //操作日志
        $ActionLogModel = new ActionLogModel();
        $actionLogList = $ActionLogModel->where('uid', $uid)->order('id desc')->limit(10)->select();
        $this->assign('actionLogList', $actionLogList);

        return $this->fetch('viewUser');
    }

    //强制修改用户密码
    public function changePwd()
    {
        $uid = input('uid/d');
        if ($uid === 1) {
            $this->error('super admin error!');
        }

        if (request()->isAjax()) {
            $newPwd = input('post.newPwd', '');

            $data = input('post.');
            if (!isset($data['newRePwd'])) {
                $data['newRePwd'] = $newPwd;
            }
            if (!validate('User')->scene('changePwd')->check($data)) {
                $this->error(validate('User')->getError());
            }

            $UserModel = new UserModel();
            $res = $UserModel->modifyPassword($uid, $newPwd);
            if ($res) {
                $this->success('成功修改密码');
            } else {
                $this->error('密码修改失败');
            }
        }

        $user = UserModel::get($uid);
        if (!$user) {
            $this->error('用户不存在');
        }
        $this->assign('uid', $uid);
        $this->assign('user', $user);

        return $this->fetch('changePwd');
    }

    //冻结用户
    public function freeze()
    {
        $uid = input('uid/d',0);
        if ($uid == 0) {
            $this->error('参数uid错误');
        }

        $UserModel = new UserModel();
        $res = $UserModel->where('id', $uid)->where('status', UserModel::STATUS_ACTIVED)->setField('status', UserModel::STATUS_FREEZED);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //激活用户
    public function active()
    {
        $uid = input('uid/d',0);
        if ($uid == 0) {
            $this->error('参数uid错误');
        }
        $UserModel = new UserModel();
        $res = $UserModel->where('id', $uid)->setField('status', UserModel::STATUS_ACTIVED);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //每日新增用户
    public function newUsers()
    {
        $timeStart = input('param.timeStart', date('Y-m-d 0:0:0'));
        $timeEnd = input('param.timeEnd', date('Y-m-d 23:59:59'));
        if ($timeEnd <= $timeStart) {
            $timeEnd = date('Y-m-d 23:59:59', strtotime($timeStart));
        }

        $where = [
            ['register_time', 'between', [$timeStart,$timeEnd]]
        ];

        $UserModel = new UserModel();
        $list = $UserModel->whereTime($where)->order('id desc')->paginate(20,false, ['query'=>input('param.')]);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        return $this->fetch('newUsers');
    }

    //用户统计
    public function userStat()
    {
        $startTime = input('param.startTime');
        $endTime = input('param.endTime');
        if (!(isset($startTime) && isset($endTime))) {
            $startTime  = date('Y-m-d',strtotime('-7 day'));
            $endTime   = date('Y-m-d');
        }

        $startDatetime = date('Y-m-d 00:00:00', strtotime($startTime));
        $endDatetime = date('Y-m-d 23:59:59', strtotime($endTime));

        $where = [
            ['register_time', 'between', [$startDatetime, $endDatetime]]
        ];

        $UserModel = new UserModel();
        $count = $UserModel->where($where)->count();
        $list = $UserModel->where($where)->order('id desc')->paginate(20, false, ['query'=>input('param.')]);

        $startTimestamp = strtotime($startTime);
        $endTimestamp = strtotime($endTime);

        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('startTimestamp', $startTimestamp);
        $this->assign('endTimestamp', $endTimestamp);
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->assign('pages', $list->render());

        return $this->fetch('userStat');
    }

    public function echartShow()
    {
        $option =[
            'xAxis'=> ['data'=>[]],
            'series'=> [['data'=>[]]],
        ];

        $where = [];
        $timeStart = input('param.start');
        $timeEnd = input('param.end');

        $UserModel = new UserModel();
        for ($i = $timeStart ; $i <= $timeEnd; $i += (24*3600)) {
            $day = date('m-d',$i);
            $beginTime = mktime(0, 0, 0, date('m',$i), date('d',$i), date('Y',$i));
            $endTime = mktime(23, 59, 59, date('m',$i), date('d',$i), date('Y',$i));

            unset($where);
            $where[] = ['register_time','between', [date_time($beginTime), date_time($endTime)]];
            $inquiryCount = $UserModel->where($where)->count();

            array_push($option['xAxis']['data'], $day);
            array_push($option['series'][0]['data'], $inquiryCount);
        }

        $this->success('success', '', $option);
    }

    //给用户发送邮件
    public function sendMail()
    {
        $data = input('post.');
        $check = $this->validate($data, ['uid'=>'require|gt:0','title'=>'require','content'=>'require']);
        if ($check !== true) {
            $this->error($check);
        }

        $uid = input('post.uid/d');
        $title = input('post.title/s');
        $content = input('post.message/s');

        $UserModel = new UserModel();
        $toUser = $UserModel->where('id', $uid)->value('email');
        $res = send_mail($toUser, $title, $content);
        if ($res) {
            $this->success('邮件已发送');
        } else {
            $this->error('邮件发送失败');
        }
    }

    //推送消息
    public function pushMessage()
    {
        $check = $this->validate(input('post.'),['uid'=>'require|gt:0','title'=>'require','content'=>'require']);
        if ($check !== true) {
            $this->error($check);
        }

        $uid = input('post.uid/d');
        $title = input('post.title/s');
        $content = input('post.content/s');
        $extra = "";

        $pushLogic = new PushLogic();
        $res = $pushLogic->pushToUser($uid, Message::TYPE_SYSTEM, $title, $content, $extra);
        if ($res) {
            $this->success('消息已推送');
        } else {
            $this->error('消息推送失败');
        }
    }

}
