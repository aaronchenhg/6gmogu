<?php

namespace app\api\validate;

/**
 * 会员验证器
 */
class Member extends ApiBase
{
    
    // 验证规则
    protected $rule = [
        'username'  => 'require',
        'password'  => 'require',
        'field'  => 'require',
        'value'  => 'require',
    ];

    // 验证提示
    protected $message = [
        'username.require'    => '用户名不能为空',
        'password.require'    => '密码不能为空',
        'field.require'    => '缺少参数field',
        'value.require'    => '缺少参数value',
    ];

    // 应用场景
    protected $scene = [
        
        'login'  =>  ['username','password'],
        'updateInfo'  =>  ['field','value'],
    ];
}
