<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */

namespace app\api\controller\lvyou;

use app\admin\logic\Log;
use app\common\controller\ControllerBase;
use app\common\service\pay\driver\Wxpay;
use think\Db;

class Notify extends ControllerBase
{

    /**
     * 微信支付异步通知
     *
     */
    public function wxNotify()
    {
        \think\Log::error("-----------------1111111111111",var_export(input(),true));
        $notify = new Wxpay();

         $result =  $notify->notify();
//         if($result['result_code'] == 'SUCCESS' || $result['return_code'] == 'SUCCESS'){
//        \think\Log::error("-----------------result",var_export($result,true));
//            //处理订单信息
//          $order_sn = $result['out_trade_no'];
//          $this->processOrder($result['out_trade_no'],$result);
//
//         }

    }

    /**
     * 处理订单支付回调信息
     * @param $order_sn 订单号
     * @param $data 返回信息
     * @return bool
     */
    public function processOrder($order_sn,$data)
    {
        Db::startTrans();
        try
        {
            $orderInfo = Db::name('Order')->where('order_sn', 'eq', $order_sn)->field('id,real_amount,order_status,user_id')->lock(true)->find();
            if ($orderInfo['order_status'] == 1) {
                $data['user_id'] = $orderInfo['user_id'];
                $data['paid_amount'] = ($data['total_fee'] / 100);
                // 更新订单状态
                $this->updateOrderStatus($orderInfo['id'], $data);
                // 减少商品库存
//                $this->reduceStock($orderInfo['id'], $info);
            }
            Db::commit();
            echo 'SUCCESS';
            return true;
        } catch (Exception $ex) {
            Db::rollback();
            Log::error($ex->getMessage());
            echo 'ERROR';
            return false;
        }
    }

    /**
     * 更改订单状态
     * @param $orderInfo 订单信息
     * @param $info 组装信息
     */
    public function updateOrderStatus($orderID,$info){
        $orderData = [
            'order_status' => 2, //设置订单状态付款成功：待发货
            'pay_type' => 3, //获取付款类型
            'paid_amount' => $info['paid_amount'], //获取实付总额
            'pay_sn' => $info['transaction_id'], //第三方支付号
            'pay_time' => time(), //设置订单支付时间
        ];

        //更新订单信息
        Db::name('Order')->where('id',$orderID)->update($orderData);
        // Log::error("---------03",var_export($res,true));
//          insertOrderLog($orderID,'微信在线支付成功',$info['out_trade_no'],$info['user_id'],'',2,getOrderStatusText(2));

    }



}