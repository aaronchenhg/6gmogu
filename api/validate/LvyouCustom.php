<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 16:16
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouCustom extends ApiBase
{
    protected $rule = [
        'id'            => 'number',    //
        'type'          => 'require|number',    // 类型
        'start_city'    => 'require',    // 出发城市
        'reach_city'    => 'require',    // 到达城市
        'start_date'    => 'require',    // 出发时间
        'days'          => 'require|number',
        'people_number' => 'require|number',
        'child_number'  => 'number',
        'remark'        => 'isString',
        'contact_name'  => 'require',
        'contact_phone' => 'require|isMobile',
        'sex'           => 'isString',
        'email'         => 'email',
        'address'       => 'isString',
        'contact_time'  => 'isString',
    ];

    protected $message = [
        'type.require'           => '请选择定制类型',
        'start_city.require'     => '请输入出发城市',
        'reach_city.require'     => '请输入到达城市',
        'start_date.require'     => '请选择出发时间',
        'days.require'           => '请选择出游天数',
        'people_number.require'  => '请选择出行人数',
        'contact_name.require'   => '请输入您的称呼',
        'contact_phone.require'  => '请输入联系电话',
        'contact_phone.isMobile' => '联系方式不正确',
    ];
}