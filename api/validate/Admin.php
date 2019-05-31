<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/21 0021
 */

namespace app\api\validate;


use think\Db;

class Admin extends ApiBase
{
    protected $rule = [
        'nickname' => 'require|checkNickname',
        'password' => 'require|alphaDash',
        'verify' => 'require|captcha',
    ];
    protected $message = [
        'nickname.require' => '昵称不能为空',
        'password.require' => '密码不能为空',
        'password.alphaDash' => '密码由字母，数字，下划线和破折号组成',
        'verify.require' => '验证码不能为空',
        'verify.captcha' => '验证码不正确',
    ];
    protected $scene = [
        'login' => ['nickname','password','verify'],
    ];

    protected function checkNickname($value,$rule = '',$data)
    {
        $info = Db::query('select * from shop_admin WHERE `nickname` = "'.$value.'" and `is_inside` = 1 and `status` = 1 limit 1');

        if(empty($info)) return '昵称错误';

        if(data_md5_key($data['password']) != $info[0]['password']) return '密码错误';

        return true;
    }
}