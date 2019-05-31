<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/31 17:14
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouOrder extends ApiBase
{
    protected $rule = [
        'id'        => 'require|number',    //
    ];
}