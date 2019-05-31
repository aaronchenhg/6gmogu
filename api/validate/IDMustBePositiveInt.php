<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/24 18:19
 * Copyright in Highnes
 */

namespace app\api\validate;

/**
 * 获取ID参数
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\validate
 */
class IDMustBePositiveInt extends ApiBase
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
    ];
}