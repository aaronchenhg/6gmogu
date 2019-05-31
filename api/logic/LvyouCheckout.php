<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/30 21:51
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use app\common\model\LvyouLineInsurance;
use app\common\model\LvyouLineSpec;
use app\common\model\LvyouLine;
//use app\common\model\LvyouOrder;
use app\common\model\LvyouOrderContact;
use app\common\model\LvyouOrderDetail;
use app\common\model\LvyouOrderLog;
use app\lib\exception\ForbiddenException;
use app\common\model\Member as CommonMember;
use app\common\model\MemberIntegral;
use think\Db;
use think\Exception;

use app\common\model\LvyouMemberContact;
use think\Log;

/**
 * 订单结算
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\logic
 */
class LvyouCheckout extends ApiBase
{


    /**
     * 支付
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 10:32
     * @param $param
     * @param $uid
     * @return array
     */
    public function pay($param, $uid)
    {
        $order_sn = @$param['order_sn'];

        // 判断订单是否可以支付
        $orderInfo = LvyouOrder::where(['order_sn' => $order_sn])->field("id,uid,total_amount,order_sn,order_status")->find();


        if (empty($orderInfo) || empty($order_sn)) {
            throw new ForbiddenException(['msg' => '订单数据不存在']);
        }

        if ($orderInfo['order_status'] != 1) {
            throw new ForbiddenException(['msg' => '订单不能支付']);
        }

        $total_fee = floatval($orderInfo['total_amount'] * 100);

        $openid = \app\common\model\Member::where('id', $uid)->value('openid');

        if (isWeixin()) {       // 手机端支付
            $config = array(
                'openid'     => $openid,
                'notify_url' => config('setting.site_url') . '/api/lvyou/lvnotify',
                'attach'     => 'lvyouorder',
            );
            $pay    = kpay('weixin');

            $res = $pay->pay($order_sn, $total_fee, 'jspay', $config);
        } else {
            $config = array(
                'notify_url' => config('setting.site_url') . '/api/lvyou/lvnotify',
                'body'       => "研学旅游",
                'attach'     => 'lvyouorder',
            );
            $res    = kpay('weixin')->pay($order_sn, $total_fee, 'native', $config);

            if ($res['status'] == 1) {
                $path                   = './uploads/payEwm/' . $order_sn . '.png';
                $res['info']['pay_ewm'] = config('setting.site_url') . qrcode($res['info']['code_url'], $path);
            }
        }
        return $res;
    }

    /**
     * 异步处理订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $notify
     */
    public function notifyProcess($notify)
    {
        $status = $notify['status'];
        Log::info("==========notifyProcess=========" . var_export($notify, true));
        if ($status == 1) {
            $ordersn   = $notify['info']['out_trade_no'];
            $openid    = $notify['info']['openid'];
            $transacid = $notify['info']['transaction_id'];
            $type      = $notify['info']['attach'];
            $orderInfo = LvyouOrder::where(['order_sn' => $ordersn])->field("id,uid,order_sn,total_amount,order_status")->find();
            Log::info("==========notifyProcess01=========" . var_export($orderInfo, true));
            if (empty($orderInfo)) {
                return false;
            }
            if ($orderInfo['order_status'] != 1) {
                return false;
            }
            $orderInfo['transaction_id'] = $transacid;
            $orderInfo['pay_method']     = $notify['info']['trade_type'];
//            $this->startTrans();
            try {
                switch ($type) {
                    case 'lvyouorder':
                        return $this->changeOrderPaid($orderInfo);
                        break;
                    default:
                        return $this->changeOrderPaid($orderInfo);
                        break;
                }
//                $this->commit();
            } catch (Exception $e) {
//                $this->rollback();
                $this->apiError([API_CODE_NAME => 1000005, API_MSG_NAME => $e->getMessage()]);
            }
        } else {
            return false;
        }
    }

    public function changeOrderPaid($orderInfo)
    {

        $condition['id'] = $orderInfo['id'];
        $res1            = LvyouOrder::where($condition)->update(['pay_time' => time(), 'order_status' => 10, 'pay_method' => $orderInfo['pay_method'], 'transaction_id' => $orderInfo['transaction_id']]);
        $res             = LvyouOrderLog::addLog($orderInfo['id'], $orderInfo['uid'], '订单支付成功');


        //添加积分记录
        MemberIntegral::addLog($orderInfo['uid'],'支付订单',-1,$orderInfo['total_amount']);
        //计算获得积分
        $configWhere = [
            'name' => 'get_integral_proportion',
            'status' => 1,
        ];
        $getIntegralProportion = $this->modelConfig->where($configWhere)->value('value');
        if(!empty($getIntegralProportion)){
            //获得积分
            $resIntegral = $orderInfo['total_amount'] * ($getIntegralProportion/100);
            CommonMember::increaseIntegral($orderInfo['uid'],$resIntegral);
            CommonMember::increaseTotalIntegral($orderInfo['uid'],$resIntegral);
        }


//        Log::info("==========notifyProcess02=========" . var_export($res1, true));
//        Log::info("==========notifyProcess03=========" . var_export($res, true));
        return $res1;
    }

    /**
     * 立即购买
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 23:17
     * @param $param
     * @param $uid
     * @return $this
     * @throws ForbiddenException
     */
    public function buy($param, $uid)
    {
        $this->validateLvyouCheckout->goCheck();
        $data = $this->validateLvyouCheckout->getDataByRule($param);

        $data['create_time']  = time();
        $data['uid']          = $uid;
        $data['order_status'] = 1;
        $data['order_sn']     = makeOrderNo();


//        $url = config('setting.site_url').'/api/lvyou/check/index?order_sn='.$data['order_sn'];
//        $path = './uploads/qrcode/Lvyouewm/order_sn' . $data['order_sn'] . '.png';

        $url = config('setting.site_url').'/api/Lvyou/check?order_sn='.$data['order_sn'];
        $path = './Uploads/qrcode/Lvyouewm/order_sn' . $data['order_sn'] . '.png';
        $ewm = qrcode($url,$path);

        $data['ewm'] = $ewm;

        $order = $this->getPreOrderAmount($data);

        Db::startTrans();
        try {
            //是否支持退款
            $isSupportRefund = $this->modelLvyouLine->where('id',$param['line_id'])->value('is_support_refund');
            $order['is_support_refund'] = $isSupportRefund;

            $res = LvyouOrder::create($order);
            $res['pay_money'] = $this->modelConfig->where('name','pay_money')->value('value');
            $res['pay_method'] = $this->modelConfig->where('name','pay_method')->value('value');
            // :TODO 生成订单出行人
            $contacts = $this->preOrderContact($param, $res['id']);
            LvyouOrderContact::insertAll($contacts);


            // :TODO 生成订单明细
            $line = $this->preOrderDetail($order, $res['id']);
            LvyouOrderDetail::create($line);
            // 记录日志

            LvyouOrderLog::addLog($res['id'], $uid, '创建订单');

            Db::commit();
        } catch (Exception $exception) {
            Db::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }


        return $res;
    }

    private function preOrderDetail($param, $order_id)
    {
        $line = $this->logicLvyouLine->getLineDetail(['id' => $param['line_id'],'users_id' => 0]);
//        $line = $this->logicLvyouLine->getLineDetail(['id' => $param['line_id']]);
        if (empty($line)) {
            throw new ForbiddenException([
                'msg' => '线路信息不存在'
            ]);
        }
        $data['line_id']     = $param['line_id'];
        $data['spec_id']     = $param['spec_id'];
        $data['spec_price']  = $this->getLinePrice($param);
        $data['order_id']    = $order_id;
        $data['supplier_id'] = $param['supplier_id'];
        $data['title']       = $line['title'];
        $data['sub_title']   = $line['sub_title'];
        $data['price']       = $line['price'];
        $data['image']       = $line['image'];
        $data['days']        = $line['days'];
        $data['start_city']  = $line['start_city'];
        $data['reach_city']  = $line['reach_city'];
        $data['fit_crowd']   = $line['fit_crowd'];
        $data['theme']       = $line['theme'];
        $data['category']    = $line['category'];
        $data['create_time'] = time();

        return $data;
    }

    /**
     * 生成出行人信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 23:55
     * @param $param 订单数据
     * @param $order_id 订单号
     * @return false|mixed|\PDOStatement|string|\think\Collection
     * @throws ForbiddenException
     */
    private function preOrderContact($param, $order_id)
    {
        $contacts = LvyouMemberContact::where(['id' => ['in', $param['contact_id']]])->field("name,mobile,qq,icard,sex")->select();
        if (empty($contacts)) {
            throw new ForbiddenException([
                'msg' => '出行人信息不存在'
            ]);
        }
        $time     = time();
        $contacts = json_decode(json_encode($contacts), true);
        foreach ($contacts as &$val) {
            $val['order_id']    = $order_id;
            $val['create_time'] = $time;
        }

        return $contacts;
    }


    /**
     * getPreOrder
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 23:22
     * @param $param
     * @return mixed
     */
    private function getPreOrderAmount($data)
    {

        $order                     = $data;
        $total_number              = $data['people_number'] + $data['child_number'];
        if($total_number <= 0){
            return CodeBase::errorMessage(200010,'请至少选择一个人');
        }

        $insurance_amount          = LvyouLineInsurance::where(['id' => $data['insurance_id']])->value('amount');
        $line_amount               = $this->getLinePrice($data);
        $order['insurance_amount'] = $insurance_amount * $total_number;
        $order['line_amount']      = $line_amount * $total_number;
        $order['promot_amount']    = 0;
        $order['supplier_id']      = LvyouLine::where(['id' => $data['line_id']])->value('supplier_id');
        $order['total_amount']     = ($order['line_amount'] + $order['insurance_amount']);

        return $order;
    }

    /**
     * 获取线路价格
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/14 10:06
     * @param $param
     * @return mixed
     */
    private function getLinePrice($param)
    {
        // :TODO 根据日期获取线路对应的价格
        $price = LvyouLineSpec::where(['id' => $param['spec_id']])->value('price');

        if (empty($price)) {
            $price = LvyouLine::where(['id' => $param['line_id']])->value('price');
        }

        return $price;
    }
}