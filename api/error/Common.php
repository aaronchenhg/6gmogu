<?php

namespace app\api\error;

class Common
{
    
    public static $passwordError            = [API_CODE_NAME => 1010001, API_MSG_NAME => '登录密码错误'];
    
    public static $usernameOrPasswordEmpty  = [API_CODE_NAME => 1010002, API_MSG_NAME => '用户名或密码不能为空'];
    
    public static $registerFail             = [API_CODE_NAME => 1010003, API_MSG_NAME => '注册失败'];
    
    public static $oldOrNewPassword         = [API_CODE_NAME => 1010004, API_MSG_NAME => '旧密码或新密码不能为空'];
    
    public static $changePasswordFail       = [API_CODE_NAME => 1010005, API_MSG_NAME => '密码修改失败'];

    public static $userInfoNull       = [API_CODE_NAME => 1010001, API_MSG_NAME => '用户信息不能为空'];

    public static $userInfoGetFail       = [API_CODE_NAME => 1010001, API_MSG_NAME => '获取用户信息失败'];

    public static $getWxInfoFail       = [API_CODE_NAME => 1010001, API_MSG_NAME => '获取用户微信信息失败'];

    public static $addUserWxInfoFail       = [API_CODE_NAME => 1010001, API_MSG_NAME => '注册小程序用户失败'];

    public static $tokenNull       = [API_CODE_NAME => 1020000, API_MSG_NAME => '请登录'];

    public static $encryptedDataNull    = [API_CODE_NAME => 1020000, API_MSG_NAME => '用户手机号码信息丢失'];

    public static $ivNull               = [API_CODE_NAME => 1020000, API_MSG_NAME => '绑定手机号信息丢失'];

    public static $bindPhoneFail               = [API_CODE_NAME => 1020000, API_MSG_NAME => '绑定手机号码失败'];
}
