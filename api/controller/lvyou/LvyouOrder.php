<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/30 21:39
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\logic\Token;

/**
 * 旅游订单
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\controller\lvyou
 */
class LvyouOrder extends ApiBase
{

    /**
     * 立即购买
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 11:02
     * @return mixed
     */
    public function buy()
    {
        $data = $this->logicLvyouCheckout->buy($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    public function pay()
    {
        $data = $this->logicLvyouCheckout->pay($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 订单列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/31 16:28
     * @return mixed
     */
    public function lists()
    {
        $data = $this->logicLvyouOrder->getOrderList(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($data);
    }

    /**
     * 订单详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/31 16:28
     * @return mixed
     */
    public function detail()
    {

        $data = $this->logicLvyouOrder->getOrderDetail($this->param);

        return $this->apiReturn($data);
    }

    /**
     * 订单数量汇总
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:05
     * @return mixed
     */
    public function orderTotal()
    {
        $data = $this->logicLvyouOrder->getOrderCountList($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 取消订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:03
     */
    public function cancel()
    {
        $data = $this->logicLvyouOrder->cancelOrder($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }

    /**
     * 关闭订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:03
     * @return mixed
     */
    public function close()
    {
        $data = $this->logicLvyouOrder->closeOrder($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }

    /**
     * 订单退款
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:05
     */
    public function applyRefund()
    {
        $data = $this->logicLvyouOrder->applyRefundOrder($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }

    /**
     * 删除订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:18
     * @return mixed
     */
    public function deleteOrder()
    {
        $data = $this->logicLvyouOrder->deleteOrder($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }

    /**
     * 订单评价
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function addComment()
    {
        $data = $this->logicLvyouOrder->addComment($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }


    /**
     * getOrderCommentList
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 17:13
     */
    public function getOrderCommentList()
    {
        $data = $this->logicLvyouOrder->getOrderCommentList(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($data);
    }


    /**
     * 获取订单状态
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 16:03
     */
    public function getOrderStatus()
    {
        $data = $this->logicLvyouOrder->getOrderStatus($this->param, Token::getCurrentUid());

        return $this->apiReturn($data);
    }
}