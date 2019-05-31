<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 16:23
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouOrderComment extends ApiBase
{
    // 验证规则
    protected $rule = [
        'order_id'     => 'require|number',
        'content'      => 'require',
        'is_anonymous' => 'number|in:0,1',
        'score'        => 'number|in:1,2,3,4,5',

    ];

    // 验证提示
    protected $message = [
        'order_id.require' => '缺少参数order_id',
        'order_id.number'  => 'order_id必须是数字',
        'content.require'  => '输入评论内容',
    ];
}