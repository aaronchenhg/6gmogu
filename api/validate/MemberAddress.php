<?php

namespace app\api\validate;

/**
 * 会员地址验证器
 */
class MemberAddress extends ApiBase
{

    // 验证规则
    protected $rule = [
        'id'         => 'require|number',
        'username'   => 'require',
        'mobile'     => 'require|isMobile',
        'province'   => 'require',
        'city'       => 'require',
        'county'     => 'require',
        'address'    => 'require',
        'is_default' => 'max:9999',
    ];

    // 验证提示
    protected $message = [
        'id.require'       => '缺少参数ID',
        'id.number'        => 'ID必须是数字',
        'username.require' => '请输入姓名',
        'mobile.require'   => '请输入手机号',
        'mobile.isMobile'   => '手机号格式错误',
        'province.require' => '请选择省',
        'city.require'     => '请选择市',
        'county.require'   => '请选择区县',
        'address.require'  => '请输入详细地址',
        'is_default.max'   => '是否默认',
    ];

    // 应用场景
    protected $scene = [

        'add'  => ['username', 'mobile', 'province', 'city', 'county', 'address', 'is_default'],
        'edit' => ['username', 'mobile', 'province', 'city', 'county', 'address', 'is_default', 'id'],
        'del'  => ['id'],
    ];
}
