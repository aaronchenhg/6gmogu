<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\validate;


class Notice extends ApiBase
{
    protected $rule = [
        'noticedata' => 'require|eq:0',
        'noticenum' => 'require|in:5,10,20'
    ];
    protected $message = [
        'noticedata.require' => '公告内容来源必填',
        'noticedata.eq' => '公告内容来源错误',
        'noticenum.require' => '公告内容读取数量必填',
        'noticenum.in' => '公告内容读取数量错误',
    ];
    protected $scene = [
        'first' => ['noticedata','noticenum']
    ];
}