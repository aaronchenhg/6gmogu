<?php

namespace app\api\validate;

/**
 * 会员地址验证器
 */
class MemberInvoice extends ApiBase
{

    // 验证规则
    protected $rule = [
        'id'         => 'require|number',
        'user_id'   => 'require',
        'invoice_type'     => 'require',    // 发票类型（1.电子发票 2.纸质发票）
        'invoice_rise'   => 'require',      // 发票抬头（1.个人 2.单位）
        'invoice_title'       => 'require', // 发票抬头
        'invoice_no'     => 'require',      // 发票税号
        'email'    => 'require|email',            // 邮箱
        // :TODO 增值税发票
    ];

    // 验证提示
    protected $message = [
        'id.require'       => '缺少参数ID',
        'id.number'        => 'ID必须是数字',
        'invoice_type.require' => '发票类型',
        'invoice_rise.require'   => '发票抬头类型',
        'invoice_title.require' => '请输入发票抬头',
        'invoice_no.require'     => '请输入发票税号',
        'email.require'   => '请输入邮箱',
    ];

    // 应用场景
    protected $scene = [

        'add'  => ['invoice_type', 'invoice_rise', 'invoice_title', 'invoice_no', 'email'],
        'edit'  => ['invoice_type', 'invoice_rise', 'invoice_title', 'invoice_no', 'email','id'],
        'del'  => ['id'],
    ];
}
