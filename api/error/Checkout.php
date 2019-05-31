<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/29 0029
 */


namespace app\api\error;


class Checkout
{
    public static $idIsNull = [API_CODE_NAME => 1000005,         API_MSG_NAME => '缺少参数ID'];
    public static $notDispatch = [API_CODE_NAME => 1000005,         API_MSG_NAME => '不在配送范围内'];
    public static $orderInfoNull = [API_CODE_NAME => 1000005,         API_MSG_NAME => '临时订单信息丢失'];
    public static $outMaxBuyNumber = [API_CODE_NAME => 1000005,         API_MSG_NAME => '超过单次购买最大数量'];
    public static $outAllMaxBuyNumber = [API_CODE_NAME => 1000005,         API_MSG_NAME => '超过购买商品最大数量'];
    public static $notRepeatCreateOrder = [API_CODE_NAME => 1000005,         API_MSG_NAME => '请勿重复下单'];

    public static function createError($errorCode = '1000005',$msg)
    {
        return [API_CODE_NAME => $errorCode, API_MSG_NAME => $msg];
    }
}