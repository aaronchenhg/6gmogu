<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;
use think\Controller;

class Pay extends ApiBase
{

    /**
     * 订单支付
     * @return mixed
     */
    public function orderPay(){

        $order_sn = input('order_sn');

        $from_type = input('from_type','1');
        $res = $this->logicOrder->orderPay(Token::getCurrentUid(),$order_sn,$type='wxapp',$from_type);

        return $this->apiReturn($res);
    }

    /**
     * 夺宝订单支付
     * User: 李姣
     * @param order_sn 订单号码
     * @return mixed
     * Date: ${DATE}
     */
    public function duobaoOrderPay()
    {
        $order_sn = input('order_sn');

        $from_type = input('from_type','1');

        $res = $this->logicDuobaoOrder->duobaoOrderPay(Token::getCurrentUid(),$order_sn,$type='wxapp',$from_type);

        return $this->apiReturn($res);
    }



}