<?php
/**
 * Created by PhpStorm.
 * User: chenhongjin
 * Date: 2018/6/14
 * Time: 16:04
 */

namespace app\api\error;


class Member
{
    public static $idNull  = [API_CODE_NAME =>5050002, API_MSG_NAME => '缺少参数id'];

    public static $maximum  = [API_CODE_NAME =>5050003, API_MSG_NAME => '超过最大领取次数'];

    public static $CouponNull  = [API_CODE_NAME =>5050004, API_MSG_NAME => '优惠券已经被抢完啦'];

    public static function usernameOrPasswordEmpty($errorCode = '',$msg = '')
    {
        return [API_CODE_NAME =>$errorCode, API_MSG_NAME => $msg];
    }
}