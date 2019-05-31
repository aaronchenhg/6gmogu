<?php
/**
 * Created by PhpStorm.
 * User: chenhongjin
 * Date: 2018/6/14
 * Time: 16:04
 */

namespace app\api\error;


class Order
{
    public static $orderAlreadyCancel  = [API_CODE_NAME =>6050001, API_MSG_NAME => '订单已取消'];

    public static $orderAlreadyFinish  = [API_CODE_NAME =>6050002, API_MSG_NAME => '订单已收货'];

    public static $remindNumTopLimit  = [API_CODE_NAME =>6050003, API_MSG_NAME => '今日提醒次数已达上限'];

    public static $orderSnError  = [API_CODE_NAME =>6050004, API_MSG_NAME => '订单号有误'];

    public static $orderInfoError  = [API_CODE_NAME =>6050005, API_MSG_NAME => '订单信息有误'];

    public static $returnMoney  = [API_CODE_NAME =>605007, API_MSG_NAME => '退款金额大于可退金额'];

    public static function usernameOrPasswordEmpty($errorCode = '',$msg = '')
    {
        return [API_CODE_NAME =>$errorCode, API_MSG_NAME => $msg];
    }
}