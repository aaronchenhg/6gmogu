<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: chenhg <945076855@qq.com>
 * Date: 2018/6/27 23:11
 */

namespace app\api\controller\v1;

/**
 *  结算
 */
use app\api\controller\ApiBase;
use app\api\logic\Token;

class Checkout extends ApiBase
{
    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }

    /**
     * 立即购买
     * @goods_id  商品ID
     * @sku_id 规格id
     * @number 商品数量
     * @address_id 收货地址信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function buynow()
    {
        $result = $this->logicCheckout->placeOrder('buynow',$this->param,$this->user_id);

        return  $this->apiReturn($result);
    }

    /**
     * 购物车流程
     * @ids 购物车IDS
     * @address_id  收货地址信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function checkout()
    {
        $result = $this->logicCheckout->placeOrder('checkout',$this->param,$this->user_id);
        return $this->apiReturn($result);
    }

    /**
     * 邮费
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function sendfee()
    {
        //收货地址信息
        $address_id = input('address_id');
        //临时订单号
        $order_sn = input('order_sn');

        $result = $this->logicCheckout->getSendFee($order_sn,$address_id,'change');
        return $this->apiReturn($result);
    }

    /**
     * 创建订单
     * @orderPrice 总金额
     * @address_id 收货地址信息
     * @remark 买家备注
     * @order_sn 临时订单号
     * @invoice 1:电子发票 2：纸质发票
     * @invoice_type 发票类型1:单位  2：个人
     * @invoice_raised  发票抬头
     * @invoice_tax 增值税号
     * @invoice_email 买家邮箱号码
     * @coupon_id 优惠券ID
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function createOrder()
    {
        $result = $this->logicCheckout->createOrder($this->user_id,$this->param);

        return $this->apiReturn($result);
    }


    /**
     * 立即下单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function pay()
    {

    }
}