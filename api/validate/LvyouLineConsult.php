<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 16:16
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouLineConsult extends ApiBase
{
    protected $rule = [
        'id'            => 'number',    //
        'line_id'       => 'require|number',    // 线路ID
        'content'       => 'require',    // 咨询内容
        'reply_content' => 'isString',    // 到达城市
    ];

    protected $message = [
        'line_id.require'       => '线路编号必填',
        'content.require'       => '请输入咨询内容',
        'reply_content.require' => '请输入回复内容',
    ];
}