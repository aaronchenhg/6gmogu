<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 17:38
 * Copyright in Highnes
 */

namespace app\api\validate;


class Register extends ApiBase
{
    protected $rule = [
        'mobile'   => 'require|isMobile',
        'password' => 'require|alphaDash|confirm',
        'code'     => 'require',
    ];

    protected $message = [
        'mobile.require'   => '请输入手机号',
        'password.require' => '请输入密码',
        'password.confirm' => '两次密码不一致',
        'code.require'     => '请输入验证码',
    ];

    protected $scene = [
        'login' => ['mobile'],
        'register'   => ['mobile', 'password','code'],
    ];
}