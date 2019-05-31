<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 20:27
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouGuide extends ApiBase
{
    protected $rule = [
        'id'        => 'number',    //
        'title'     => 'require',   // 标题
        'sub_title' => 'isString',    // 摘要
        'source'    => 'number',    // 来源
        'category'  => 'require',   // 游记类型
        'image'     => 'isString',    //封面图
        'banner'     => 'isString',    //封面图
        'author'    => 'isString',    // 作者
        'content'   => 'require',
        'uid'       => 'number',
        'tags'       => 'isString',
        'keywords'       => 'isString',
        'status'       => 'number',
        'pagesize'       => 'isString',
        'categroy'       => 'isString',
    ];

    protected $message = [
        'title.require'     => '请输入游记标题',
//        'sub_title.require' => '请输入游记摘要',
        'category.require'  => '请选择游记类型',
        'content.require'   => '请输入游记内容',
    ];
}