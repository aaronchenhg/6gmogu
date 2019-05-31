<?php

namespace app\api\error;

class CodeBase
{

    public static $idIsNull              = [API_CODE_NAME => 1000005,         API_MSG_NAME => '缺少参数ID'];

    public static $success              = [API_CODE_NAME => 0,         API_MSG_NAME => '操作成功'];

    public static $failure              = [API_CODE_NAME => -1,         API_MSG_NAME => '操作失败'];

    public static $accessTokenError     = [API_CODE_NAME => 1000001,   API_MSG_NAME => '访问Toekn错误'];

    public static $userTokenError       = [API_CODE_NAME => 1000002,   API_MSG_NAME => '用户Toekn错误'];

    public static $apiUrlError          = [API_CODE_NAME => 1000003,   API_MSG_NAME => '接口路径错误'];

    public static $dataSignError        = [API_CODE_NAME => 1000004,   API_MSG_NAME => '数据签名错误'];

    public static $noticeError        = [API_CODE_NAME => 1000004,   API_MSG_NAME => '公告数据错误'];

    public static function errorMessage($errorCode = '',$msg = '')
    {
        return [API_CODE_NAME =>$errorCode, API_MSG_NAME => $msg];
    }

}
