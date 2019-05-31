<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 20:21
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouLine extends ApiBase
{
    protected $rule = [
        'id'         => 'number',
        'start_city' => 'number',
        'reach_city' => 'number',
        'reach_school' => 'number',
        'theme'      => 'number',
        'fit_crowd'  => 'number',
        'keywords'   => 'isString',
        'key'        => 'isString',
        'line_id'    => 'number',
        'category'   => 'number',
        'is_hot'     => 'number',
        'is_new'     => 'number',
        'pagesize'     => 'number',
    ];
}