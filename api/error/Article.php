<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\error;


class Article
{
    public static function noticeError($errorCode = '1030001',$msg = '')
    {
        return [API_CODE_NAME => $errorCode, API_MSG_NAME => $msg];
    }
}