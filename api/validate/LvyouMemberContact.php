<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/28 0:14
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouMemberContact extends ApiBase
{
    protected $rule = [
        'id'        => 'require',    //
        'name'      => 'require',    // 姓名
        'sex'       => 'require|in:1,2,3',    // 出发城市
        'mobile'    => 'require|isMobile',    // 手机号
        'card_type' => 'require|in:1,2,3',    // 类型
        'icard'     => 'require',    // 证件号
    ];

    protected $message = [
        'name.require'      => '请输姓名',
        'sex.require'       => '请选择性别',
        'mobile.require'    => '请输入联系电话',
        'mobile.isMobile'   => '联系方式不正确',
        'card_type.require' => '请选择证据类型',
        'icard.require'     => '请输入证据号',
    ];

    protected $scene = [
        'add'  => ['name', 'sex', 'mobile', 'card_type', 'icard'],
        'edit' => ['name', 'sex', 'mobile', 'card_type', 'icard', 'id'],
        'del'  => ['id'],
    ];
}