<?php

namespace app\api\validate;

/**
 * 验证器
 */
class SetDelivery extends ApiBase
{
    
    // 验证规则
    protected $rule = [
        'id'  => 'require',
        'express_name'  => 'require',
        'express_sn'  => 'require',
        'moblie'  => 'require',
        'describe'  => 'max:100',
    ];

    // 验证提示
    protected $message = [
        'id.require'    => '缺少参数ID',
        'express_name.require'    => '请选择物流公司',
        'moblie.require'    => '请填写联系电话',
        'express_sn.require'    => '请填写物流单号',
        'describe.max'    => '退款说明超过最大字数',
    ];

    // 应用场景
    protected $scene = [
        
        'delivery'  =>  ['id','express_name','express_sn','describe','moblie'],
    ];
}
