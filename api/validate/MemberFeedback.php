<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\validate;


class MemberFeedback extends ApiBase
{
    protected $rule = [
        'type_id' => 'require|number',
        'type_var' => 'require',
        'content' => 'require|min:10',
    ];
    protected $message = [
        'type_id.require' => '缺少参数type_id',
        'type_id.number' => 'type_id必须为正整数',
        'type_var.require' => '类型文字不能为空',
        'content.require' => '请填写你的反馈内容',
        'content.min' => '最少填写10个字',
    ];
    protected $scene = [
        'addFeedback' => ['type_id','type_var','content'],
        'add'=>['content']
    ];

}