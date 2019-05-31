<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/7 14:35
 * Copyright in Highnes
 */

namespace app\api\validate;


class MemberInfo extends ApiBase
{
    // 验证规则
    protected $rule = [
        'headimgurl' => 'isString',
        'nickname'   => 'isString',
        'realname'   => 'isString',
        'birthday'   => 'isString',
        'sex'        => 'number',
        'email'        => 'email',
    ];
}