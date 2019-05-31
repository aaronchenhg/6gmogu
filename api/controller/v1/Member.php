<?php

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;

/**
 * 会员接口控制器
 */
class Member extends ApiBase
{

    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }

    /**
     * 获取会员信息
     * @return mixed
     */
    public function memberInfo()
    {
        $res =  $this->logicMember->getMemberInfo(Token::getCurrentUid());
        return $this->apiReturn($res);
    }

    /**
     * 更新会员信息
     */
    public function updataMemberInfo(){

        $res =  $this->logicMember->updataMemberInfo($this->user_id,$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 获取会员地址列表
     */
    public function memberAddressList(){

        $res = $this->logicMember->getMemberAddressList($this->user_id,$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 获取会员地址详细
     */
    public function memberAddressDetailed(){
        $id = input('id');
        $res = $this->logicMember->getMemberAddressDetailed($id);
        return $this->apiReturn($res);
    }

    /**
     * 添加会员地址
     * @return mixed
     */
    public function addMemberAddress(){

        $res =  $this->logicMember->addMemberAddress($this->user_id,$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 编辑会员地址
     * @return mixed
     */
    public function editMemberAddress(){

        $res =  $this->logicMember->editMemberAddress($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 设置会员默认地址
     * @return mixed
     */
    public function setDefaultAddress(){

        $res =  $this->logicMember->setDefaultAddress($this->user_id,$this->param);
        return $this->apiReturn($res);
    }


    /**
     * 删除会员地址
     * @return mixed
     */
    public function delMemberAddress(){

        $res =  $this->logicMember->delMemberAddress($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 获取用户收藏列表
     */
    public function memberFavoriteList(){

        $res =  $this->logicMember->getMemberFavoriteList($this->user_id,$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 收藏/取消商品
     */
    public function setGoodsFavorite(){

        $goods_id = input('id');
        $res =  $this->logicMember->setGoodsFavorite($this->user_id,$goods_id);
        return $this->apiReturn($res);

    }

    /**
     * 取消商品收藏
     */
    public function cancelGoodsFavorite(){

        $ids = input('ids');
        $res =  $this->logicMember->cancelGoodsFavorite($this->user_id,$ids);
        return $this->apiReturn($res);
    }

    /**
     * 获取足迹列表
     */
    public function getMemberHistoryList(){

        $res =  $this->logicMember->getMemberHistoryList($this->user_id);

        return $this->apiReturn($res);
    }

    /**
     * 删除会员足迹
     */
    public function deleteMemberHistory(){

        $ids = input('ids');
        $res =  $this->logicMember->deleteMemberHistory($this->user_id,$ids);

        return $this->apiReturn($res);
    }

    /**
     * 优惠券领券中心
     */
    public function couponCenter(){

        $res =  $this->logicMember->getCouponCenterList();

        return $this->apiReturn($res);

    }

    /**
     * 领取优惠券
     */
    public function receiveCoupon(){

        $id = input('id');
        $res =  $this->logicMember->receiveCoupon($this->user_id,$id);

        return $this->apiReturn($res);
    }

    /**
     * 会员优惠券列表
     */
    public function memberCouponList(){

        $type = input('type',1);

        $res =  $this->logicMember->memberCouponList($this->user_id,$type);

        return $this->apiReturn($res);

    }

    /**
     * 储存formID
     */
    public function saveFormid(){

        $formid = input('formid');

        $res =  $this->logicMember->saveFormid($this->user_id,$formid,2);

        return $this->apiReturn($res);

    }


}
