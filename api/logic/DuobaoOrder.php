<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9 0009
 * Time: 14:23
 */

namespace app\api\logic;
use app\api\error\CodeBase;
use app\common\service\pay\driver\Wxpay;
use app\api\error\DuobaoOrder as DuobaoOrderError;
use app\api\error\Order as OrderError;
use think\Db;
use think\Config;
use think\Log;

class DuobaoOrder extends ApiBase
{
    /**
     * 创建夺宝订单
     * User: 李姣
     * @param array $data
     * @param id 活动ID
     * @param number 购买次数
     * @param $user_id 用户ID
     * @return array
     * Date: 2018/08/09
     */
    public function placeOrder($data = [],$user_id)
    {
        $data['address_id'] = 0;
        if(empty($data['id'])) return DuobaoOrderError::$idIsNull;
        if(empty($data['number'])) return DuobaoOrderError::$numberIsNull;
        if(empty($data['mobile'])) return DuobaoOrderError::$mobileIsNull;

        $duobao_item_info = $this->modelDuobaoItem->getInfo(['id'=>$data['id']],'id,duobao_id,goods_id,spec_id,has_lottery,is_start,status,max_buy,min_buy,current_buy,user_max_buy,unit_price,current_buy');

        if($duobao_item_info['has_lottery'] == 1) return DuobaoOrderError::$awardedPrize;
        if($duobao_item_info['min_buy'] > $data['number']) return DuobaoOrderError::$lessMinBuyNumber;
        if($duobao_item_info['user_max_buy'] > 0 && $data['number'] > $duobao_item_info['user_max_buy']) return DuobaoOrderError::$greaterMaxBuyNumber;
        if($duobao_item_info['status'] != 1) return DuobaoOrderError::$duobaoItemDisable;
        if(($duobao_item_info['current_buy'] + $data['number']) > $duobao_item_info['max_buy']) return DuobaoOrderError::$greaterMaxBuyNumber;

        $order_info = $this->setOrderArray($duobao_item_info,$user_id,$data['number'],$data['mobile']);

        return $order_info;
    }

    /**
     * 添加订单信息
     * User: 李姣
     * @param $duobao_item_info 夺宝活动信息
     * @param $user_id 用户ID
     * @param $number 购买次数
     * @param int $address_id 收货地址ID
     * @return array
     * Date: 2018/08/09
     */
    private function setOrderArray($duobao_item_info,$user_id,$number,$mobile,$address_id=0)
    {
        $order['order_sn'] = makeOrderNo();
        $order['type'] = 2;
        $order['user_id'] = $user_id;
        $order['create_time'] = time();
        $order['pay_status'] = $order['order_status'] = 0;
        $order['number'] = $number;
        $order['total_price'] = $duobao_item_info['unit_price'] * $number;
        $order['address_id'] = $address_id;
        $order['buy_mobile'] = $mobile;

        Db::startTrans();

        //添加订单主信息
        $order_id = $this->modelDuobaoOrder->fetchSql(false)->insertGetId($order);

        if(!$order)
        {
            Db::rollback();
            return DuobaoOrderError::$addOrderFail;
        }
        $item['user_id'] = $user_id;
        $item['order_id'] = $order_id;
        $item['order_sn'] = $order['order_sn'];
        $item['deal_id'] = $duobao_item_info['goods_id'];
        $item['goods_name'] = Db::name('goods')->where('id',$duobao_item_info['goods_id'])->value('title');
        $item['spec_id'] = $duobao_item_info['spec_id'];
        $item['duobao_id'] = $duobao_item_info['duobao_id'];
        $item['duobao_item_id'] = $duobao_item_info['id'];
        $item['number'] = 1;
        $item['unit_price'] = $duobao_item_info['unit_price'];
        $item['create_time'] = sprintf("%.3f",THINK_START_TIME);

        // 查询未购买幸运号码（开启活动时生成的幸运号）
        $lottery_sns = $this->modelDuobaoItemLog->getColumn(['duobao_item_id'=>$duobao_item_info['id'],'is_buy'=>'0'],'lottery_sn','id','lottery_sn asc',$number);

        // 如果中奖号码使用完毕
        if(empty($lottery_sns))
        {
            return DuobaoOrderError::$addOrderFail;
        }
        $update_log['order_id'] = $order_id;
        $update_log['create_time'] = time();
        $update_log['user_id'] = $user_id;

        if($number > 1)//购买次数大于1
        {
            foreach ($lottery_sns as $k=>$v)
            {
                $item['lottery_sn'] = $v;
                $order_item_id = $this->modelDuobaoOrderItem->insertGetId($item);

                if(!$order_item_id)
                {
                    Db::rollback();
                    return DuobaoOrderError::$addOrderItemFail;
                }

                $update_log['order_item_id'] = $order_item_id;
                $update_log['is_buy'] = 2;

                if(!$this->modelDuobaoItemLog->updateInfo(['id'=>$k],$update_log))
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateItemLogFail;;
                }
            }
            //更新活动购买数量
            if(!$this->modelDuobaoItem->where('id',$duobao_item_info['id'])->setInc('current_buy',$number))
            {
                Db::rollback();
                return DuobaoOrderError::$updateItemBuyNumberFail;
            }
        }else
        {
            $item['lottery_sn'] = array_values($lottery_sns)[0];

            //添加订单详细信息
            $order_item_id = $this->modelDuobaoOrderItem->insertGetId($item);
            if(!$order_item_id)
            {
                Db::rollback();
                return DuobaoOrderError::$addOrderItemFail;
            }
            $update_log['order_item_id'] = $order_item_id;
            $update_log['is_buy'] = 2;

            //将购买的活动信息号码更改为已购买
            if(!$this->modelDuobaoItemLog->updateInfo(['id'=>array_keys($lottery_sns)[0]],$update_log))
            {
                Db::rollback();
                return DuobaoOrderError::$updateItemLogFail;
            }
            //更新活动购买数量
            if(!$this->modelDuobaoItem->where('id',$duobao_item_info['id'])->setInc('current_buy','1'))
            {
                Db::rollback();
                return DuobaoOrderError::$updateItemBuyNumberFail;
            }
        }
        Db::commit();
        return $order;
    }

    public function setFailSave($data = [],$user_id)
    {
        Log::error('====quxiao0====='.var_export($data,true));
        Db::startTrans();
        if(isset($data['order_sn']) && !empty($data['order_sn']))
        {
            $this->modelDuobaoOrder->alias('a');

            $join = [
                ['duobao_order_item b','a.id = b.order_id'],
                ['shop_duobao_item d','b.duobao_item_id = d.id'],
                ['duobao_item_log c','c.lottery_sn = b.lottery_sn'],
            ];
            $field = 'd.id as duobao_item_id,c.is_buy,a.order_status,a.create_time,a.pay_status,a.number,b.lottery_sn,a.pay_status,a.id as order_id';
            $order_info = $this->modelDuobaoOrder->getInfo(['a.order_sn' => $data['order_sn'],'c.user_id'=>$user_id,'a.is_delete'=>0],$field,$join);
            Log::error('====quxiao====='.var_export($order_info,true));
//            print_r($order_info);exit;
            if($order_info['is_buy'] == 2 && $order_info['pay_status'] == 0)
            {
                if($this->modelDuobaoOrder->query("UPDATE shop_duobao_order SET is_delete = 1 WHERE id =  ".$order_info['order_id']) === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateBuyNumberFail;
                }

                $sql  = "UPDATE shop_duobao_item SET current_buy = current_buy - ".$order_info['number']." WHERE id =  ".$order_info['duobao_item_id'];
                Log::error('====quxiao1====='.var_export($sql,true));
                $item_result = $this->modelDuobaoItem->query($sql);

                if($item_result === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateBuyNumberFail;
                }

                $log_result = $this->modelDuobaoItemLog->query("UPDATE shop_duobao_item_log SET is_buy = 0 WHERE duobao_item_id =  ".$order_info['duobao_item_id'] .' and lottery_sn = '.$order_info['lottery_sn'].'');
                if($log_result === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateIsBuyFail;
                }
            }
        }else
        {
            $last_one = time()-90;

            $this->modelDuobaoOrder->alias('a');

            $join = [
                ['duobao_order_item b','a.id = b.order_id'],
                ['shop_duobao_item d','b.duobao_item_id = d.id'],
                ['duobao_item_log c','c.lottery_sn = b.lottery_sn'],
            ];
            $field = 'd.id as duobao_item_id,a.user_id,a.total_price,a.pay_amount,c.is_buy,a.order_status,a.create_time,a.pay_status,a.number,b.lottery_sn,c.id as lottery_id,a.id as order_id';

            $where = ['a.create_time' => ['<',$last_one],'a.pay_status'=>0,'c.is_buy'=>2,'a.is_delete'=>0];

            $order_info = $this->modelDuobaoOrder->alias('a')->join($join)->where($where)->field($field)->select();
            Log::error('====quxiaolists====='.var_export($order_info,true));

            foreach ($order_info as $k=>$value)
            {
                if($this->modelDuobaoOrder->query("UPDATE shop_duobao_order SET is_delete = 1 WHERE id =  ".$value['order_id']) === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateBuyNumberFail;
                }
               $sql = "UPDATE shop_duobao_item SET current_buy = current_buy - ".$value['number']." WHERE id =  ".$value['duobao_item_id'];
                Log::error('====quxiao2====='.var_export($sql,true));
                if($this->modelDuobaoItem->query($sql) === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateBuyNumberFail;
                }

                if($this->modelDuobaoItemLog->query("UPDATE shop_duobao_item_log SET is_buy = 0 WHERE duobao_item_id =  ".$value['duobao_item_id'] .' and lottery_sn = '.$value['lottery_sn'].'') === false)
                {
                    Db::rollback();
                    return DuobaoOrderError::$updateIsBuyFail;
                }
            }
        }

        Db::commit();
        return CodeBase::$success;
    }

    /**
     * 生成微信支付订单
     * User: 李姣
     * @param $user_id 用户信息ID
     * @param $order_sn 订单号码
     * @param string $type 判断微信还是公众号
     * @param $from_type
     * @return array
     * Date: 2018/08/10
     */
    public function duobaoOrderPay($user_id,$order_sn,$type='wxapp',$from_type)
    {
        if (!$order_sn || empty($order_sn)) {
            return OrderError::$orderSnError;
        }
        //用户信息
        $user_info = $this->modelMember->getInfo(['id' => $user_id],'id,openid,openid_mini,mobile');

        //订单信息
        $order_info = $this->modelDuobaoOrder->getInfo(['order_sn' => $order_sn],'id,order_sn,user_id,total_price,pay_amount');

        if(empty($order_info)){
            return OrderError::$orderInfoError;
        }
        $Wxpay = new Wxpay();

//        $from_type = cache('from_type');

        if($type == 'wxapp'){
            $order['body'] = '电商';
            $order['out_trade_no'] = $order_info['order_sn'];
            $order['total_fee'] = $order_info['total_price'];
            $order['trade_type'] = 'JSAPI';
            $order['from_type'] = $from_type;
            $order['user_id'] = $order_info['user_id'];
            $order['openid'] = $from_type == 1 ? $user_info['openid'] : $user_info['openid_mini']; //1是公众号 ，2是小程序
            $order['attach'] = 'duobao';
        }

        try{
            $resWx = $Wxpay->pay($order,'wxapp');

        }catch (Exception $e){
            halt($e->getMessage());
            return CodeBase::$failure;

        }

        return ['orderInfo' => $order_info, 'resWx'=>$resWx];
    }

    /**
     * 获取用户中奖信息
     * User: 李姣
     * @param $data
     * @param lottery_sn 中奖号码
     * @param order_sn 订单号码
     * @param $user_id 用户ID
     * @return array
     * Date: 2018/08/13
     */
    public function getPrizeInfo($data,$user_id)
    {
        $order_item_info = $this->checkPrizeInfo($data,$user_id);
        if(isset($order_item_info['code'])) return $order_item_info;

        $this->modelDuobaoOrderItem->alias('a');

        $field = 'a.id,a.delivery_status,c.pay_status,c.id as order_id,a.deal_id,a.spec_id ';
        $where = ['a.order_sn'=>$data['order_sn'],'b.show_sn'=>$data['lottery_sn'],'a.is_luck'=>1];
        if(empty($order_item_info['spec_id']))//没有规格信息
        {
            $join = [
                ['duobao_item_log b','a.lottery_sn = b.lottery_sn'],
                ['duobao_order c','a.order_id = c.id'],
                ['goods g','a.deal_id = g.id']
            ];
            $where['g.id'] = $order_item_info['deal_id'];
            $field .= ',g.title,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config::get('http_name').'/'.'",g.thumb)))as thumb,"" as option_title,"" as option_img,g.market_price';
        }else //商品有规格信息
        {
            $join = [
                ['duobao_item_log b','a.lottery_sn = b.lottery_sn'],
                ['duobao_order c','a.order_id = c.id'],
                ['goods g','a.deal_id = g.id'],
                ['goods_option go','a.spec_id = go.id']
            ];
            $where['g.id'] = $order_item_info['deal_id'];
            $where['go.id'] = $order_item_info['spec_id'];
            $field .= ',g.title,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config::get('http_name').'/'.'",g.thumb)))as thumb,go.title as option_title,
            IF(go.thumb = "","",IF(LOCATE("http", go.thumb) > 0,go.thumb,CONCAT("'.Config::get('http_name').'/'.'",go.thumb)))as option_img,go.market_price';
        }
        $order_item_info = $this->modelDuobaoOrderItem
            ->getInfo($where,$field,$join);

        $order_item_info['address_id'] = $this->modelMemberAddress->getValue(['user_id'=>$user_id,'is_default'=>1,'status'=>1]);
        return $order_item_info;
    }
    /**
     * 领奖逻辑
     * User: 李姣
     * @param $data
     * @param address_id 地址ID
     * @param $user_id
     * Date: 2018/08/13
     */
    public function takePrize($data,$user_id,$from_type)
    {
        $data['lottery_sn'] = $data['trulottery_sne'];
        $order_item_info = $this->checkPrizeInfo($data,$user_id);

        if(isset($order_item_info['code'])) return $order_item_info;
        if(empty($data['address_id'])) return DuobaoOrderError::$addressIdIsNull;

        $address_info = $this->modelMemberAddress->getInfo(['id'=>$data['address_id'],'user_id'=>$user_id,'status'=>1]);

        if(empty($order_item_info)) return DuobaoOrderError::$addressIdIsFail;
        if(empty($address_info)) return DuobaoOrderError::$addressIdIsFail;

        $address = $this->updateTakePrizeInfo($address_info,$order_item_info,$user_id);

        return $this->addOrderInfo($address_info,$order_item_info,$user_id,$from_type);
    }

    /**
     * 验证获奖信息
     * User: 李姣
     * @param $data
     * @param order_sn 订单号码
     * @param lottery_sn 中奖号码
     * @param $user_id 用户ID
     * @return array
     * Date: 2018/08/13
     */
    private function checkPrizeInfo($data,$user_id)
    {
        if(empty($data['order_sn'])) return DuobaoOrderError::$duobaoOrderSnNull;
        if(empty($data['lottery_sn'])) return DuobaoOrderError::$lotterySnNull;

        $this->modelDuobaoOrderItem->alias('a');

        $join = [
            ['duobao_item_log b','a.lottery_sn = b.lottery_sn'],
            ['duobao_order c','a.order_id = c.id'],
            ['order o','o.duobao_order_id = c.id','LEFT'],
        ];
        $field = 'a.id,a.delivery_status,c.pay_status,c.id as order_id,a.deal_id,a.spec_id,c.pay_amount,c.pay_sn,c.total_price,c.pay_time,o.id as ling_id,o.order_status';

        $order_item_info = $this->modelDuobaoOrderItem
            ->getInfo(['a.order_sn'=>$data['order_sn'],'b.show_sn'=>$data['lottery_sn'],'a.is_luck'=>1],$field,$join);

        if((!empty($order_item_info['ling_id'])) && (in_array($order_item_info['order_status'],[1,2,3,4]))) return DuobaoOrderError::$isPrized;
        if(empty($order_item_info)) return DuobaoOrderError::$prizeSnFail;

        //判断发货状态
        if(!empty($order_item_info['delivery_status'])) return DuobaoOrderError::$deliveryStatusFail;

        //判断订单是否已支付
        if($order_item_info['pay_status'] != 2) return DuobaoOrderError::$notPayStatus;

        return $order_item_info;
    }

    /**
     * 修改领奖记录
     * User: 李姣
     * @param $address_info
     * @param $order_item_info
     * @param $user_id
     * @return array
     * Date: ${DATE}
     */
    private function updateTakePrizeInfo($address_info,$order_item_info,$user_id)
    {
        $update_info['address_id'] = $address_info['id'];
        $update_info['address_name'] = $address_info['username'];
        $update_info['address_mobile'] = $address_info['mobile'];
        $update_info['address_detail'] = $address_info['address'];
        $update_info['delivery_status'] = 0;

        if(!$this->modelDuobaoOrder->updateInfo(['id'=>$order_item_info['order_id']],$update_info))
        {
            return DuobaoOrderError::$addAdressFail;
        }
        //添加领奖日志记录
        insertDuobaoOrderLog('用户'.$user_id.'领奖成功中奖',$order_item_info['order_id'],$order_item_info['pay_amount']);

        return $address_info;
    }
    private function addOrderInfo($address_info,$order_item_info,$user_id,$from_type)
    {
        $order['user_id'] = $order_goods['user_id'] = $user_id;
        $order['is_duobao'] = 1;
        $order['duobao_order_id'] = $order_item_info['order_id'];
        $order['order_sn'] = makeOrderNo();
        $order['pay_sn'] = $order_item_info['pay_sn'];
        $order['from_type'] = $from_type;
        $order['price'] = $order['goods_price'] = $order['discount_price'] = $order['real_amount'] = $order_goods['price'] = $order_goods['real_price'] = $order_goods['discount_price'] = $order_goods['total_price'] = $order_item_info['total_price'];
        $order['paid_amount'] = $order_goods['real_price'] = $order_item_info['pay_amount'];
        $order['freight'] = $order_goods['send_status'] = 0;
        $order['order_status'] = 2;
        $order['pay_type'] = 4;
        $order['address_id'] = $address_info['id'];
        $order['address_name'] = $address_info['username'];
        $order['address_mobile'] = $address_info['mobile'];
        $order['address_detail'] = $address_info['address'];
        $order['refund_status'] = -1;
        $order['create_time'] = $order_goods['create_time'] = time();
        $order['pay_time'] = $order_item_info['pay_time'];
        empty($order_item_info['spec_id']) ? $order['has_option'] = 0 : $order['has_option'] = 1;
        $order['uniacid'] = $order_goods['uniacid'] = Config::get('uniacid');

        $order_id = $this->modelOrder->insertGetId($order);
        if(!$order_id) return DuobaoOrderError::$addGoodsOrderFail;

        $order_goods['order_id'] = $order_id;
        $order_goods['goods_id'] = $order_item_info['deal_id'];
        $order_goods['goods_name'] = $this->modelGoods->getValue(['id'=>$order_item_info['deal_id']],'title');
        $order_goods['sku_id'] = $order_item_info['spec_id'];
        !empty($order_item_info['spec_id']) ? $order_goods['sku_name'] = $this->modelGoodsOption->getValue(['id'=>$order_item_info['spec_id']],'title') : $order_goods['sku_name'] = '' ;


        $order_id = $this->modelOrderGoods->insertGetId($order_goods);
        if(!$order_id) return DuobaoOrderError::$addGoodsOptionOrderFail;

        return $order;
    }
}