<?php
/**
 * Created by PhpStorm.
 * User: Dh106
 * Date: 2018/5/24
 * Time: 17:51
 */

namespace app\api\logic;


use app\api\model\WxUser;
use think\Db;
use think\Log;

class SendTemplateMessage extends WxMessage
{

    public function __construct($userid,$templateid,$formid,$data,$page='',$keyword='',$type=1,$first='',$remark='')
    {
//        $this->touser = $openid;
        $openid = $this->getUserOpenID($userid);
        $this->tplID = $templateid;
        $this->formID = $formid;
        $this->data = $this->prepareMessageData($data,$type,$first,$remark);
        $this->page = $page;
        $this->emphasisKeyWord = $keyword;
        Log::error("---------消息openid:----".var_export($openid,true));
        Log::error("---------消息formid:----".var_export( $this->formID,true));
        Log::error("---------消息data:----".var_export($this->data,true));
        Log::error("---------消息tempid:----".var_export($this->tplID,true));
//        return parent::sendMessage($openid);
        $this->sendMessagesss($type,$openid);
    }

    /**
     * 发送模板消息
     * @param $type 1.小程序 2.公众号
     * @param $openid
     * @return bool
     */
    public function sendMessagesss($type,$openid){
        if($type ==1){
            return parent::sendMessage($openid);
        }elseif ($type == 2){
            return parent::sendMessagePublicNumber($openid);
        }
    }


    private function prepareMessageData($data,$type,$first,$remark)
    {
        //type 1.小程序模板消息 2.公众号模板消息
        if($type == 1){
            $datalen = count($data);
            $formatdata = [];
            for($i=1;$i<=$datalen;$i++) {
                $formatdata['keyword'.$i]['value'] = $data[$i-1];
            }
        }elseif ($type == 2){
            $datalen = count($data);
            $formatdata = [];
            $formatdata['first']['value'] = $first;
            $formatdata['first']['color'] = 'red';
            for($i=1;$i<=$datalen;$i++) {
                $formatdata['keyword'.$i]['value'] = $data[$i-1];
                $formatdata['keyword'.$i]['color'] = '#189bdc';
            }
            $formatdata['remark']['value'] = $remark;
            $formatdata['remark']['color'] = '#999999';

        }

        return $formatdata;
    }

    /**
     * 根据用户id获取openid
     * @param $userid
     * @return mixed
     * @throws \think\Exception\DbException
     */
    public function getUserOpenID($userid)
    {
        $user = Db::name('Member')->where('id',$userid)->value('openid');
        if (!$user) {
            return false;
        }
        return $user;
    }
}