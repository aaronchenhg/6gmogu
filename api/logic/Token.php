<?php

namespace app\api\logic;


use app\common\model\Member as MemberModel;
use app\lib\exception\NoLoginException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Log;
use think\Request;

class Token
{
    protected  static $debug =false;
    /**
     * 生成Token
     * @author: chenhg <945076855@qq.com>
     * @return string
     */
    public static function generateToken()
    {
        //32个字符组成一组随机字符串
        $randChars = getRandChar(32);
        //用三组字符串，进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('setting.token_salt');

        return md5($randChars . $timestamp . $salt);
    }

    /**
     * 获取当前TOKEN  根据key 获取值
     * @author: chenhg <945076855@qq.com>
     * @param $key key
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentTokenVar($key)
    {
        if(self::$debug){
          return  MemberModel::where([])->order('id desc')->value($key);
        }
        $input = input();

        if (isset($input['token']) && !empty($input['token'])) {
            $inputToken = $input['token'];
        }
        if (empty($inputToken)) {
            $token = Request::instance()->header('token');
        } else {
            $token = $inputToken;
        }
//        $token = Request::instance()->header('token');
        $vars = Cache::get($token);


        if (!$vars) {

            throw new NoLoginException(['data' => ['url' => config('setting.site_url') . config('setting.login_url')]]);
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if(isWeixin()){
                if (!MemberModel::userExistByOpenid($vars['openid'])) {
                    cache($token, null);
                    $vars = null;
                }
            }else{
                if (!MemberModel::userExistByMobile($vars['mobile'])) {
                    cache($token, null);
                    $vars = null;
                }
            }

            if (!$vars) {

                throw new NoLoginException(['data' => ['url' => config('setting.site_url') . config('setting.login_url')]]);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }

    /**
     * 获取当前uid
     * @author: chenhg <945076855@qq.com>
     * @return mixed
     */
    public static function getCurrentUid()
    {
        //token
        $uid = self::getCurrentTokenVar('id');
        return $uid;
    }


    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if ($exist) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 保存用户信息
     * @author: chenhg <945076855@qq.com>
     * @param $cachedValue
     * @return string
     * @throws TokenException
     */
    public static function saveToCache($cachedValue)
    {
        $key       = self::generateToken();
        $value     = json_encode($cachedValue);
        $expire_in = config('setting.token_expire_in');

        $request = cache($key, $value, $expire_in);
        if (!$request) {
            throw new TokenException([
                'msg'       => '服务器缓存异常',
                'errorCode' => 10005,
                'data'      => ['url' => config('setting.site_url') . config('setting.login_url')]
            ]);
        }
        return $key;
    }

    public static function getCurrentCache($token)
    {

        $vars = Cache::get($token);
        if (!is_array($vars)) {
            $vars = json_decode($vars, true);
        }
        return $vars;
    }
}