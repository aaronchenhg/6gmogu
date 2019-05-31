<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\logic\Token;

/**
 * 会员接口控制器
 */
class Member extends ApiBase
{

    /**
     * 获取会员信息
     * @return mixed
     */
    public function memberInfo()
    {
        $res = $this->logicMember->getMemberInfo(Token::getCurrentUid());
        return $this->apiReturn($res);
    }

    /**
     * 更新会员信息
     */
    public function updateMember()
    {

        $res = $this->logicMember->updateMember(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 更新会员信息
     */
    public function updataMemberInfo()
    {

        $res = $this->logicMember->updataMemberInfo(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }


    /**
     * 获取用户收藏列表
     */
    public function memberFavoriteList()
    {

        $res = $this->logicMember->getMemberFavoriteList(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 收藏/取消商品
     */
    public function setGoodsFavorite()
    {

        $goods_id = input('id');
        $res      = $this->logicMember->setGoodsFavorite(Token::getCurrentUid(), $goods_id);
        return $this->apiReturn($res);

    }

    /**
     * 取消商品收藏
     */
    public function cancelGoodsFavorite()
    {

        $ids = input('ids');
        $res = $this->logicMember->cancelGoodsFavorite(Token::getCurrentUid(), $ids);
        return $this->apiReturn($res);
    }

    /**
     * 获取足迹列表
     */
    public function getMemberHistoryList()
    {

        $res = $this->logicMember->getMemberHistoryList(Token::getCurrentUid());

        return $this->apiReturn($res);
    }

    /**
     * 删除会员足迹
     */
    public function deleteMemberHistory()
    {

        $ids = input('ids');
        $res = $this->logicMember->deleteMemberHistory(Token::getCurrentUid(), $ids);

        return $this->apiReturn($res);
    }

    /**
     * 优惠券领券中心
     */
    public function couponCenter()
    {

        $res = $this->logicMember->getCouponCenterList();

        return $this->apiReturn($res);

    }

    /**
     * 领取优惠券
     */
    public function receiveCoupon()
    {

        $id  = input('id');
        $res = $this->logicMember->receiveCoupon(Token::getCurrentUid(), $id);

        return $this->apiReturn($res);
    }

    /**
     * 会员优惠券列表
     */
    public function memberCouponList()
    {

        $type = input('type', 1);

        $res = $this->logicMember->memberCouponList(Token::getCurrentUid(), $type);

        return $this->apiReturn($res);

    }

    /**
     * 储存formID
     */
    public function saveFormid()
    {

        $formid = input('formid');

        $res = $this->logicMember->saveFormid(Token::getCurrentUid(), $formid, 2);

        return $this->apiReturn($res);

    }


    /********************************************** 新增接口 **/


    /**
     * 会员积分记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 23:08
     * @return mixed
     */
    public function getMemberIntegralList()
    {
        $data = $this->logicMemberIntegral->getMemberIntegralList(Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 获取会员等级列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 0:15
     * @return mixed
     */
    public function getMemberLevelList()
    {
        $data = $this->logicMemberLevel->getMemberLevelList();
        return $this->apiReturn($data);
    }

    /**
     * 我的收藏列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/9 16:43
     * @return mixed
     */
    public function getFavoriteList()
    {
        $data = $this->logicMemberFavorite->getFavoriteList($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);

    }

    /**
     * 删除收藏
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/9 16:42
     * @return mixed
     */
    public function deleteFavorite()
    {
        $data = $this->logicMemberFavorite->deleteFavorite($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);

    }


    /**
     * 我的消息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 1:20
     * @return mixed
     */
    public function getSystemMessageList()
    {
        $data = $this->logicMemberMessage->getSystemMessageList(Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 我的消息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 1:20
     * @return mixed
     */
    public function getSystemMessageDetail()
    {
        $data = $this->logicMemberMessage->getSystemMessageDetail($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }


    public function bindEmail()
    {
        $data = $this->logicMemberInfo->bindEmail($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }


    public function bindMobile()
    {
        $data = $this->logicMemberInfo->bindMobile($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 生成商品详细二维码
     */
    public function createGoodsDetailedEwm(){

        $goodsID = input('goods_id');

        $url = config('setting.site_url').'/shop/#/goodsdetails?goods_id='.$goodsID;
        $path = './Uploads/qrcode/Lvyouewm/goods_id' . $goodsID . '.png';
        $ewmUrl = qrcode($url,$path);
        $data['ewmUrl'] = config('setting.site_url').$ewmUrl;

        return $this->apiReturn($data);
    }


}
