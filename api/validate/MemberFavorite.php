<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/6 23:38
 * Copyright in Highnes
 */

namespace app\api\validate;


class MemberFavorite extends ApiBase
{
    protected $rule = [
        'id'            => 'number',    //
        'goods_id'       => 'require|number',    // 线路ID
    ];

    protected $message = [
        'goods_id.require'       => '数据id',
    ];
}