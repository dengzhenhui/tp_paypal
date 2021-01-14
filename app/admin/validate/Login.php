<?php


namespace app\admin\validate;


class Login extends BaseValidate
{

    protected $rule =   [
        'username'  => 'require|length:3,25',
        'password' => 'require|length:6,18',
        'code'     => 'require'
    ];

    protected $message  =   [
        'username.require'   => '用户名必填',
        'password.require'   => '密码必填',
        'password.length'  => '密码长度在6-18之间',
        'code.require'  => '验证码必填'
    ];
}