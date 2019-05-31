<?php

namespace app\api\logic;

use think\Log;

/**
 *
 */
class WxMessage
{

//    private $sendUrl = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s"; //小程序发送模板消息
    private $sendUrl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s"; //公众号发送模板消息

    private $touser;
    //不让子类控制颜色
    private $color = 'black';

    protected $tplID;
    protected $page;
    protected $formID;
    protected $data;
    protected $emphasisKeyWord;

    function getToken()
    {
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        Log::error("---------token:----".var_export($token,true));
        $this->sendUrl = sprintf($this->sendUrl, $token['access_token']);//这里有坑$token['access_token']

        Log::error("---------sendUrl:----".var_export($this->sendUrl,true));
    }

    // 开发工具中拉起的微信支付prepay_id是无效的，需要在真机上拉起支付
    protected function sendMessage($openID)
    {
        Log::error("-----------------我是测试模板消息通啦002".var_export(55555555,true));
        $this->getToken();
        $data = [
            'touser' => $openID,
            'template_id' => $this->tplID,
            'page' => $this->page,
            'form_id' => $this->formID,
            'data' => $this->data,
            //            'color' => $this->color,
            'emphasis_keyword' => $this->emphasisKeyWord
        ];
        Log::error("---------发送消息1:----".var_export($data,true));
        $result = curl_post($this->sendUrl, $data);
        $result = json_decode($result, true);
        Log::error("---------发送消息2:----".var_export($result,true));

        if ($result['errcode'] == 0) {
            (new Member())->setNumFormid($this->formID);
            return true;
        } else {
            Log::error("---------模板消息发送失败:----".var_export($result['errmsg'],true));
        }
    }

    protected function sendMessagePublicNumber($openID)
    {
        Log::error("-----------------我是测试模板消息通啦002Public".var_export(55555555,true));
        $this->getToken();
        $data = [
            'touser' => $openID,
            'template_id' => $this->tplID,
            'url' => 'http://huabenwx.micmark.com/#/index',
            'data' => $this->data,
            //            'color' => $this->color,
        ];
        Log::error("---------发送消息1Public:----".var_export($data,true));
        $result = curl_post($this->sendUrl, $data);
        $result = json_decode($result, true);
        Log::error("---------发送消息2Public:----".var_export($result,true));

        if ($result['errcode'] == 0) {
            (new Member())->setNumFormid($this->formID);
            return true;
        } else {
            Log::error("---------模板消息发送失败Public:----".var_export($result['errmsg'],true));
        }
    }

}


