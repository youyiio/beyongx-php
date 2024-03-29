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
        'id'        => ['require', 'integer'],
        'nickname'   => ['require', 'max' => 32],
        'roleIds'    => ['array'],
        'account'    => ['require'],
        'email'      => ['email'],
        'password'   => ['require', 'min' => 6, 'max' => 16],
        'repassword' => ['require', 'confirm:password'],
        'sex'        => ['require', 'in' => [1, 2]],
        'born'       => ['date'],
        'qq'         => ['regex' => '/^[1-9][0-9]{5,}$/'],
        'weixin'     => ['regex' => '/^[a-zA-Z][a-zA-Z\d_-]{5,19}$/'],
        'mobile'     => ['require', 'regex' => '/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|16[6]|(17[0,3,5-8])|(18[0-9])|19[89])\d{8}$/'],
        'phone'      => ['regex' => '/^(\d{3,4}-)?\d{7,8}$/'],
        'headUrl'    => ['regex' => '/\.(png|jpg|gif|jpeg|webp)$/'],
        'website'    => ['url'],

        // 'newPwd'   => ['require','min'=> 6, 'max'=> 16, 'checkNewPwd'],
        // 'newRePwd' => ['require','confirm:newPwd'],
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
        $temp = $UserModel->where('id', session('uid'))->field('password,salt')->find();
        if ($temp['password'] == encrypt_password($value, $temp['salt'])) {
            return '新密码与旧密码一致';
        }
        return true;
    }

    protected $message = [
        'id'               => '用户id错误',
        'account.require'   => '账户名必填',
        'nickname.require' => '昵称必填',
        'nickname.max'     => '昵称最多32个字符',
        'roleIds.require'  => '角色ID必填',
        'email.email'      => '邮箱格式错误',
        'password.require' => '密码必填',
        'password.min'     => '密码最少6个字符',
        'password.max'     => '密码最多16个字符',
        'repassword'       => '两次密码不一致',
        'code.require'     => '验证码必填',
        'code.regex'       => '验证码为6位数字',
        'sex.require'      => '性别必填',
        'sex.in'           => '性别选择有误',
        'born'             => '出生日期有误',
        'qq'               => 'QQ号有误',
        'weixin'           => '微信号格式错误',
        'headUrl'          => '图片格式错误',
        'mobile.require'   => '手机号必填',
        'phone'            => '电话号码有误',
        'website'          => '网址有误',
    ];

    protected $scene = [
        'create' => ['nickname', 'mobile', 'email', 'password', 'roleIds'], //新增用户
        'edit' => ['nickname', 'email', 'roleIds', 'qq', 'weixin'],
        'ucenterEdit' => ['nickname', 'qq', 'weixin', 'sex', 'headUrl', 'mobile', 'email'],
        'profile' => ['nickname', 'sex', 'born', 'qq', 'mobile', 'phone', 'website'],
        'modifyPassword' => ['id', 'password'],
        'addRoles' => ['id', 'roleIds']
    ];
}
