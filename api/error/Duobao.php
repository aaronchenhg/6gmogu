<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/16 0016
 * Time: 11:45
 */

namespace app\api\error;


class Duobao
{
    public static function duobaoError($msg,$code = 6010002)
    {
        return [API_CODE_NAME => $code, API_MSG_NAME => $msg];
    }
}