<?php
namespace app\common\model;


class UserVerifyCodeModel extends BaseModel
{
    protected $name = CMS_PREFIX . 'user_verify_code';
    protected  $pk = 'id';

    const STATUS_UNUSED = 1; //未使用
    const STATUS_USED = 2;  //已使用

    const TYPE_REGISTER = 'register';
    const TYPE_RESET_PASSWORD = 'reset_password';
    const TYPE_MAIL_ACTIVE = 'mail_active';

    //自动完成
    protected $auto = ['update_time'];
    protected $insert = ['create_time'];
    protected $update = [];

    //$effectiveTime有效时间，单位秒
    public function createVerifyCode($type, $target, $code, $effectiveTime)
    {
        $data['type'] = $type;
        $data['target'] = $target;
        $data['status'] = UserVerifyCodeModel::STATUS_UNUSED;
        $data['code'] = $code;

        $createTime = date_time();
        $expireTime = strtotime($createTime) + $effectiveTime;

        $data['expire_time'] = date_time($expireTime);
        $data['create_time'] = $createTime;

        $model = UserVerifyCodeModel::create($data);

        return $model;
    }

    public function findLatestByTarget($type, $target)
    {
        $where["type"] = $type;
        $where["target"] = $target;

        $userVerifyCode = $this->where($where)->order('id desc')->find();
        if (!$userVerifyCode) {
            return false;
        }

        return $userVerifyCode;
    }

    public function setCodeUsed($codeId)
    {
        $where['id'] = $codeId;
        $data['status'] = UserVerifyCodeModel::STATUS_USED;
        $count = $this->isUpdate(true)->save($data, $where);

        return $count > 0;
    }
}