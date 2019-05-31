<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;

/**
 * 订单接口控制器
 */
class Order extends ApiBase
{

    /**
     * 获取订单列表
     */
    public function orderList(){
        $order_status = input('order_status',0);
        $res = $this->logicOrder->getOrderList($this->user_id,$order_status);
        return $this->apiReturn($res);
    }

    /**
     * 获取订单详细
     */
    public function orderDetailed(){
        $id = input('id');
        $res = $this->logicOrder->getOrderDetailed($id);
        return $this->apiReturn($res);
    }

    /**
     * 取消订单
     */
    public function cancelOrder(){
        $id = input('id');
        $reason = input('cancel_reason');
        $res = $this->logicOrder->cancelOrder($id,$reason);
        return $this->apiReturn($res);
    }

    /**
     * 订单收货(完成订单)
     */
    public function finishOrder(){
        $id = input('id');
        $res = $this->logicOrder->finishOrder($id);
        return $this->apiReturn($res);
    }

    /**
     * 删除订单（改变状态假删除）
     */
    public function deleteOrder(){
        $id = input('id');
        $res = $this->logicOrder->deleteOrder($id,$this->user_id);
        return $this->apiReturn($res);
    }

    /**
     * 提醒发货
     */
    public function remindSendGoods(){

        $id = input('id');
        $order_sn = input('order_sn');
        $res = $this->logicOrder->remindSendGoods($this->user_id,$id,$order_sn);
        return $this->apiReturn($res);
    }

    /**
     * 查看物流
     */
    public function getDeliveryInfo(){
        $express_sn = input('express_sn');
        $res = $this->logicOrder->getDeliveryInfo($express_sn);
        return $this->apiReturn($res);
    }


    /**
     * 发票列表
     */
    public function invoiceList(){

        $res = $this->logicOrder->invoiceList($this->user_id);
        return $this->apiReturn($res);
    }


    /**
     * 获取订单取消原因文字
     */
    public function getOrderReasonText(){

        $type = input('type',1);
        $res = $this->logicOrder->getOrderReasonText($type);
        return $this->apiReturn($res);
    }

    /**
     * 获取评价列表
     */
    public function getEvaluateList(){

        $res = $this->logicOrder->getEvaluateList($this->user_id);
        return $this->apiReturn($res);
    }

    /**
     * 申请订单退换货
     */
    public function returnOrder(){

        $res = $this->logicOrder->returnOrder($this->user_id,$this->param);
        return $this->apiReturn($res);

    }

    /**
     * 退换货订单列表
     */
    public function returnOrderList(){
        $res = $this->logicOrder->returnOrderList($this->user_id);
        return $this->apiReturn($res);
    }

    /**
     * 退换货订单详细
     * @return mixed
     */
    public function returnOrderDetailed(){
        $id = input('id');
        $res = $this->logicOrder->returnOrderDetailed($id);
        return $this->apiReturn($res);
    }

    /**
     * 申请订单退款
     */
    public function applyOrderRefund(){
        $id = input('id');
        $reason = input('reason');
        $res = $this->logicOrder->applyOrderRefund($this->user_id,$id,$reason);
        return $this->apiReturn($res);
    }

    /**
     * 查询首页待支付订单
     */
    public function getHomeWaitPayOrder(){

        $res = $this->logicOrder->getOrderList($this->user_id,1,false);

        return $this->apiReturn($res);

    }

    /**
     * 获取物流公司
     */
    public function expressList(){

        $list = $this->logicOrder->expressList();

        return $this->apiReturn($list);
    }

    /**
     * 填写物流公司信息
     */
    public function setDelivery(){

        $res = $this->logicOrder->setDelivery($this->param);
        return $this->apiReturn($res);
    }

    public function returnGoodsDelivery(){

    }

}
