<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\validate;


class ReturnOrder extends ApiBase
{
    protected $rule = [
        'rec_id' => 'require',
        'order_id' => 'require',
        'order_sn' => 'require',
        'goods_status' => 'require',
        'reason' => 'require',
        'type' => 'require',
        'refund_money' => 'require',
        'describe' => 'max:100',
    ];
    protected $message = [
        'rec_id.require' => '缺少参数rec_id',
        'order_id.require' => '缺少参数order_id',
//        'order_sn.require' => '缺少参数order_sn',
        'rec_id.require' => '缺少参数rec_id',
        'goods_status.require' => '请选择货物状态',
        'reason.require' => '请选择原因',
        'type.require' => '缺少参数type',
        'refund_money.require' => '缺少参数refund_money',
        'describe.max' => '说明最大一百字',
    ];
    protected $scene = [
        'return_money' => ['type','order_id','rec_id','goods_status','reason','describe','refund_money'],
        'return_goods' => ['type','order_id','rec_id','reason','describe','refund_money'],
        'return_type' => ['type'],
    ];
}