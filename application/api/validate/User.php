<?php
namespace app\api\validate;

use app\common\model\UserModel;
use think\Validate;
use beyong\commons\utils\PregUtils;

/**
* 用户验证规则
*/
class User extends Validate
{

    protected $rule = [
        'uid'        => ['require', 'integer'],
        'nickname'   => ['require','max'=> 32],
        'username'   => ['require','checkUsername'],
        'email'      => ['email','unique:' . 'sys_user,email'],
        'password'   => ['require','min'=> 6, 'max'=> 16],
        'repassword' => ['require','confirm:password'],
        'code'       => ['require','regex'=>'/^[0-9]{6}$/'],
        'sex'        => ['in'=> [0,1,2]],
        'born'       => ['date'],
        'qq'         => ['regex'=>'/^[1-9][0-9]{5,}$/'],
        'mobile'     => ['regex'=>'/\d{11}/'],
        'phone'      => ['regex'=>'/^(\d{3,4}-)?\d{7,8}$/'],
        'website'    => ['url'],

        'newPwd'   => ['require','min'=> 6, 'max'=> 16, 'checkNewPwd'],
        'newRePwd' => ['require','confirm:newPwd'],
    ];

    protected function checkUsername($value, $rule, $data)
    {
        if (empty($value)) {
            return '账号不能为空!';
        }
        if (isset($data['type'])) {
            $type = $data['type'];
            if ($type == "mobile" && !PregUtils::isMobile($value)) {
                return "手机号格式不正确!";
            }

            if ($type == "email" && !PregUtils::isEmail($value)) {
                return "邮箱格式不正确";
            }
        }

        return true;
    }

    protected function checkNewPwd($value, $rule, $data)
    {
        $UserModel = new UserModel();
        $oldPwd = $UserModel->where('id', session('uid'))->value('password');
        if ($oldPwd == encrypt_password($value, get_config('password_key'))) {
            return '新密码与旧密码一致';
        }
        return true;
    }

    protected $message = [
        'uid'              => '用户id错误',
        'nickname.require' => '用户名必填',
        'nickname.max'     => '用户名最多32个字符',
        'nickname.unique'  => '用户名已存在',
        'email.email'      => '邮箱格式错误',
        'email.unique'     => '邮箱已注册',
        'password.require' => '密码必填',
        'password.min'     => '密码最少6个字符',
        'password.max'     => '密码最多16个字符',
        'repassword'       => '两次密码不一致',
        'code.require'     => '验证码必填',
        'code.regex'       => '验证码为6位数字',
        'sex'              => '性别选择有误',
        'born'             => '出生日期有误',
        'qq'               => 'QQ号有误',
        'mobile'           => '手机号有误',
        'phone'            => '电话号码有误',
        'website'          => '网址有误',
        'newPwd.require' => '密码必填',
        'newPwd.min'     => '密码最少6个字符',
        'newPwd.max'     => '密码最多16个字符',
        'newRePwd'       => '两次密码不一致',
    ];

    protected $scene = [
        'register' => ['username','password','repassword','code'],
        'login' => ['username','password','code'],
        'add' => ['email','password','repassword'],
        'profile' => ['nickname','sex','born','qq','mobile','phone','website'],
        'modifyPassword' => ['password','newPwd','newRePwd'], //自己修改
        'changePwd' => ['uid','newPwd','newRePwd'], //管理员强制修改用户操作
        'resetPwd' => ['password'],
        'edit' => ['email', 'phone', 'nickname']
    ];


}
