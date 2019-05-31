<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/30 14:56
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouCheckout extends ApiBase
{
    protected $rule = [
        'line_id'       => 'require|number',
        'play_date'     => 'require|date',
        'spec_id'       => 'number',
        'insurance_id'  => 'number',
        'people_number' => 'number',
        'child_number'  => 'number',
        'contact_id'    => 'require',
        'mobile'        => 'require|isMobile',
        'email'         => 'email',
        'remark'        => 'isString',
        'amount'        => 'float',
    ];

    protected $message = [
        'line_id.require'       => '请选择旅游线路',
        'play_date.require'     => '请选择集合日期',
        'spec_id.require'       => '请选择套餐',
//        'insurance_id.require'  => '请选择保险',
        'people_number.number' => '成人数必须为数字',
        'child_number.number' => '小孩人数必须为数字',
        'contact_id.require'    => '请选择出行人',
//        'mobile.require'        => '请输入联系电话',
        'email.email'           => '邮箱不正确',
    ];

}