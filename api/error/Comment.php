<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\error;


class Comment
{
    public static function commentError($errorCode = '1060001',$msg = '')
    {
        return [API_CODE_NAME => $errorCode, API_MSG_NAME => $msg];
    }

    public static $alreadyCmment = [API_CODE_NAME =>1060003, API_MSG_NAME => '已评论'];
}