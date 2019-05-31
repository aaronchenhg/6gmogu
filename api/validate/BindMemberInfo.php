<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/7 18:38
 * Copyright in Highnes
 */

namespace app\api\validate;


class BindMemberInfo extends ApiBase
{

    // 验证规则
    protected $rule = [
        'mobile' => 'require|isMobile',
        'code'   => 'require',
        'email'  => 'require|email',
    ];

    protected $message = [
        'mobile.require' => '请输入手机号',
        'email.isMobile' => '手机号格式错误',
        'email.require'  => '请输入邮箱',
        'email.email'    => '邮箱格式错误',
    ];
    protected $scene = [
        'mobile'     => ['mobile'],
        'email'      => ['email'],
        'bindEmail'  => ['email', 'code'],
        'bindMobile' => ['mobile', 'code'],
    ];
}