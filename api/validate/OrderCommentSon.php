<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\validate;


class OrderCommentSon extends ApiBase
{
    protected $rule = [
        'comment_id' => 'require',
        'content' => 'require',
        'type' => 'require',
        'images' => 'max:1000000',
    ];
    protected $message = [
        'comment_id.require' => '缺少参数comment_id',
        'content.require' => '请输入内容',
        'type.require' => '缺少参数type',
    ];
    protected $scene = [
        'comment_son' => ['comment_id','content','type'],
    ];
}