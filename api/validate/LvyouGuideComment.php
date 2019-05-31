<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/26 0:10
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouGuideComment extends ApiBase
{
    protected $rule = [
        'id'         => 'number',    //
        'guide_id'   => 'require|number',   // 攻略id必须有
        'content'    => 'require',    // 评论内容必须有
        'reply_id' => 'require|number',    // 评论内容必须有
        'uid'        => 'number',
    ];

    protected $message = [
        'content.require'  => '请输入游记评论内容',
        'reply_id.require' => '评论id不能为空',
        'reply_id.number' => '评论id格式错误',
        'guide_id.require' => '攻略id不能为空',
    ];

    protected $scene = [
        'comment' => ['content', 'guide_id'],
        'reply' => ['content', 'reply_id'],
    ];
}