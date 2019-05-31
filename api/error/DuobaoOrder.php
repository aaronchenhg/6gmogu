<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9 0009
 * Time: 14:27
 */

namespace app\api\error;


class DuobaoOrder
{
    public static $idIsNull = [API_CODE_NAME => 6010001, API_MSG_NAME => '夺宝活动信息必须'];
    public static $numberIsNull = [API_CODE_NAME => 6010001,API_MSG_NAME => '购买数量不能为空'];
    public static $awardedPrize = [API_CODE_NAME => 6010001,API_MSG_NAME => '活动已开奖'];
    public static $lessMinBuyNumber = [API_CODE_NAME => 6010001,API_MSG_NAME => '小于用户最小下单次数'];
    public static $greaterMaxBuyNumber = [API_CODE_NAME => 6010001,API_MSG_NAME => '大于用户最大下单次数'];
    public static $duobaoItemDisable = [API_CODE_NAME => 6010001,API_MSG_NAME => '该活动已禁用'];
    public static $addressIdIsNull = [API_CODE_NAME => 6010001,API_MSG_NAME => '请选择收货地址'];
    public static $addCreateFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '下单失败'];
    public static $addOrderFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '创建订单信息失败'];
    public static $addOrderItemFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '创建订单商品信息失败'];
    public static $updateBuyNumberFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '更改已销售数量失败'];
    public static $updateItemLogFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '更改幸运号信息失败'];

    public static $duobaoOrderSnNull = [API_CODE_NAME => 6010001,API_MSG_NAME => '夺宝订单号为空'];
    public static $lotterySnNull = [API_CODE_NAME => 6010001,API_MSG_NAME => '中奖号码为空'];
    public static $prizeSnFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '中奖信息错误'];
    public static $deliveryStatusFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '订单已发货'];
    public static $notPayStatus = [API_CODE_NAME => 6010001,API_MSG_NAME => '订单未支付'];
    public static $addressIdIsFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '地址信息错误'];
    public static $addAdressFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '添加收货地址失败'];

    public static $addGoodsOrderFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '合并订单主表失败'];
    public static $addGoodsOptionOrderFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '合并订单商品信息失败'];
    public static $isPrized = [API_CODE_NAME => 6010001,API_MSG_NAME => '手机号码为空'];
    public static $updateItemBuyNumberFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '更新购买人数失败'];
    public static $updateIsBuyFail = [API_CODE_NAME => 6010001,API_MSG_NAME => '更新幸运号状态失败'];
    public static $mobileIsNull = [API_CODE_NAME => 6010001,API_MSG_NAME => '手机号码不能为空'];
}