<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\error\CodeBase;
use app\api\logic\Token;
use think\Cache;
use think\Loader;
use think\Request;

/**
 * 公共基础接口控制器
 */
class Common extends ApiBase
{
    protected $authorize_url;
    protected $redirect_uri;

    public function __construct()
    {
        parent::__construct();
        $this->redirect_uri = config('setting.site_url') . '/api/lvyou/callback';
    }


    /**
     * 省市区接口
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function area()
    {
        return $this->apiReturn($this->logicCommon->getDistrictList());
    }

    public function gsearch()
    {
        return $this->apiReturn($this->logicCommon->getGoodsSearch($this->param));
    }

    /**
     * 小程序登录
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function wxappLogin()
    {
        return $this->apiReturn($this->logicCommon->wxappLogin($this->param));
    }

    /**
     * 微信H5登陆
     * @return mixed
     */
    public function h5Login($scope = 'snsapi_userinfo')
    {
        $route               = input('router'); // 跳转路由
        $this->redirect_uri  = $this->redirect_uri . '?route=' . $route;
        $this->authorize_url = sprintf(config('wx.authorize_url'), config('wx.appid'), urlencode($this->redirect_uri), $scope);

        $this->redirect($this->authorize_url);
    }

    /**
     * callback
     * @return mixed
     */
    public function callback()
    {
        $wxResult = input();
        $fail     = array_key_exists('errcode', $wxResult);
        if ($fail) {
            return CodeBase::$failure;
        }
        $this->code = input('code');

        $access_token = $this->logicCommon->getAccessTokenByCode($this->code);
        if (array_key_exists('scope', $access_token) && $access_token['scope'] == 'snsapi_base') {
            $this->scope = 'snsapi_userinfo';
            // 重新登录一次,获取微信信息
            $this->login('snsapi_userinfo');
        }

        // 添加用户信息
        $user_info = $this->logicMember->getUserinfoByOpenid($access_token['openid'], $access_token['access_token']);
        $token     = $user_info['token'];

        //判断是否有参数
        $flag = '?';
        if (strpos($wxResult['route'], '?')) {
            $flag = '&';
        }

        if (empty($wxResult['route'])) {
            $wxResult['route'] = '/Mainpage/Index';
        }

        if (strpos($wxResult['route'], 'lvyou-check')) {
            $router = $wxResult['route'] ?: 'api/lvyou/check';
            unset($wxResult['route']);
            $info = Token::getCurrentCache($token);
            session('user_id', @$info['id']);
            $url = config('setting.site_url') . url(str_replace('-', '/', $router, $wxResult));
        } else {
            $url = config('setting.site_url') . "/m/#{$wxResult['route']}{$flag}token=" . $token;
        }
        // 跳转到前段页面

        $this->redirect($url);
    }


    public function bindphone()
    {
        return $this->apiReturn($this->logicCommon->wxappBindPhone($this->param, $this->user_id));
    }

    /**
     * 上传图片
     * @throws SuccessException
     */
    public function uploadPicture用下面的方法()
    {

        $file        = $_FILES['file'];
        $upload_path = 'uploads/picture/' . date('Ymd') . '/';
        //不存在该目录则创建
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        if (move_uploaded_file($file['tmp_name'], $upload_path . $file['name'])) {
            $picture = $upload_path . $file['name'];
        } else {
            $picture = "error";
        }

        $data['data'] = config('setting.site_url') . DS . $picture;
        return $this->apiReturn($data);
    }

    /**
     * 上传图片
     */
    public function uploadPicture()
    {

        $result = $this->logicFile->pictureUpload();

        return $this->apiReturn($result);
    }

    /**
     * 获取热搜
     */
    public function getHotSearch()
    {

        $result = $this->logicSearch->getHotSearch();

        return $this->apiReturn($result);

    }

    /**
     * 微信分享
     */
    public function shareInfo()
    {
        Loader::import("WeChat.Jssdk");
//        $jssdk = new \JSSDK();
        $jssdk = new \JSSDK();
        $token = Request::instance()->header('token');
        $vars  = Cache::get($token);
        $vars  = json_decode($vars, true);

        $url = input("url") ?: 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = str_replace("θ", "#", $url); // §
        $res = $jssdk->getSignPackage($url);

        $_siteInfo = $this->getConfigVal();

        $share['title'] = $_siteInfo['title'];
        $share['desc']  = $_siteInfo['desc'];
        $share['logo']  = config("setting.site_url") . '/' . $_siteInfo['icon'];
        $share['url']   = $_siteInfo['url'];
//        $share['from_id'] = @$vars['id'];

        $_data['site']  = $share;
        $_data['jssdk'] = $res;
        return $this->apiReturn($_data);
    }

    /**
     * 获取客服电话
     */
    public function getServiceMobile()
    {

        $info                     = $this->logicSystemSite->getSettingInfo(['type' => 'setting']);
        $info                     = json_decode($info['content'], true);
        $result['service_mobile'] = $info['service_mobile'];

        return $this->apiReturn($result);
    }

    /**
     * 获取网站配置
     * @return mixed
     */
    function getConfigVal()
    {
        $result = $info = $this->logicSystemSite->getSettingInfo(['type' => 'follow_and_share']);
        $result = json_decode($result['content'], true);

        return $result;
    }


    public function sendMobileCode()
    {
        $data = $this->logicMemberInfo->sendMobileCode($this->param, $this->user_id);
        return $this->apiReturn($data);
    }

    public function sendEmailCode()
    {
        $data = $this->logicMemberInfo->sendEmailCode($this->param, $this->user_id);
        return $this->apiReturn($data);
    }
}
