<?php


namespace app\api\logic;


use think\Config;
use think\Exception;
use think\Log;

class AccessToken
{
    private $tokenUrl;
    const TOKEN_CACHED_KEY = 'access';
    const TOKEN_EXPIRE_IN = 7000;

    function __construct()
    {
        $url = config('wx.access_token_url_wx');
        $url = sprintf($url, config('wx.appid'), config('wx.appsecret'));
//        $url = sprintf($url, Config::get('appid'), Config::get('appsecret'));
        Log::error('=============url======'.var_export($url),true);
        $this->tokenUrl = $url;
    }

    // 建议用户规模小时每次直接去微信服务器取最新的token
    // 但微信access_token接口获取是有限制的 2000次/天
    public function get()
    {
        $token = $this->getFromCache();
        if (!$token)
        {
            Log::error('-------token22222------'.var_export(222),true);
            return $this->getFromWxServer();
        }
        else
        {
            return $token;
        }
    }

    private function getFromCache()
    {
        $token = cache(self::TOKEN_CACHED_KEY);

        if ($token)
        {
            return $token;
        }
        return null;
    }


    private function getFromWxServer()
    {
        $token = curl_get($this->tokenUrl);
        $token = json_decode($token, true);
        Log::error('-------getFromWxServer------'.var_export($token,true));
        if (!$token)
        {
            throw new Exception('获取AccessToken异常');
        }
        if (!empty($token['errcode']))
        {
            throw new Exception($token['errmsg']);
        }
        $this->saveToCache($token);
        return $token;
    }

    private function saveToCache($token){
        cache(self::TOKEN_CACHED_KEY, $token, self::TOKEN_EXPIRE_IN);
    }
}