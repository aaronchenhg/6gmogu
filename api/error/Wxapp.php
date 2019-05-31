<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/19 0019
 */


namespace app\api\error;


class Wxapp
{
    public static function getGoodsError($errorCode = '1050001',$msg = '')
    {
        return [API_CODE_NAME => $errorCode, API_MSG_NAME => $msg];
    }

    public static $IllegalAesKey        = [API_CODE_NAME => -41001, API_MSG_NAME => 'Fail'];
    public static $IllegalIv        = [API_CODE_NAME => -41002, API_MSG_NAME => 'Fail'];
    public static $IllegalBuffer        = [API_CODE_NAME => -41003, API_MSG_NAME => 'Fail'];
    public static $DecodeBase64Error        = [API_CODE_NAME => -41004, API_MSG_NAME => 'Fail'];
}