<?php
/**
 * Created by PhpStorm.
 * User: chenhongjin
 * Date: 2018/6/14
 * Time: 16:04
 */

namespace app\api\error;


class MemberFeedback
{
    public static $goodsIsNull  = [API_CODE_NAME =>5000001, API_MSG_NAME => '商品不存在'];

    public static $stockLack  = [API_CODE_NAME =>5000002, API_MSG_NAME => '库存不足'];

    public static function memberFeedback($errorCode = '',$msg = '')
    {
        return [API_CODE_NAME =>$errorCode, API_MSG_NAME => $msg];
    }
}