<?php

namespace app\api\logic;

use app\api\error\Order as OrderError;
use app\api\error\CodeBase;
use app\common\service\pay\driver\Wxpay;
use think\Db;
use think\Exception;
use think\Loader;

/**
 * 订单接口逻辑
 */
class Order extends ApiBase
{


    /**
     * 获取用户信息
     */
    public function getMemberInfo()
    {

        $where = [
            'id' => 1,
        ];
        $join = [];
        $field = 'id,nickname,headimgurl,sex,city,mobile,birthday';
        return $this->modelMember->getInfo($where, $field,$join);
    }


    /**
     * 获取订单列表
     * @param $user_id 会员ID
     * @param $param
     */
    public function getOrderList($user_id,$order_status,$paginate = 6,$limit = 1){

        $this->modelOrder->alias('o');
        $where['o.order_status'] = ['neq',-1];
        if($order_status){
            $where['o.order_status'] = $order_status;
        }
        $where['o.user_id'] = $user_id;
        $join = [
//            [SYS_DB_PREFIX . 'member m','m.id = o.user_id','left'],
//            [SYS_DB_PREFIX . 'member_address md','md.id = o.address_id','left'],
                [SYS_DB_PREFIX . 'express e','e.id = o.express_com_id','left'],
        ];
//        print_r($paginate);
//        print_r($limit);exit;
        $field = 'o.id,o.order_sn,o.create_time,o.cancelpay_time,o.order_status,o.evaluate_status,o.freight,o.express_sn,o.express_com,
        IF(LOCATE("http", e.logo) > 0,e.logo,CONCAT("'.Config('setting.site_url').'",e.logo))as express_logo,o.real_amount,o.refund_status';
        $lists =  $this->modelOrder->getList($where, $field, 'create_time desc', $paginate,$join,'',$limit);

        if(!empty($lists)){
            foreach ($lists as $k => $v) {
                $lists[$k]['order_goods'] = $this->modelOrderGoods
                    ->alias('og')
                    ->where('og.order_id',$v['id'])
                    ->join('goods g','g.id=og.goods_id','left')
                    ->field('og.id,og.goods_id,og.goods_name,og.sku_name,og.price,og.buy_nums,g.title,g.sub_title,og.return_goods_status
                    ,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb)))as thumb')
                    ->select();

                $lists[$k]['order_num'] = 1;
            }

        }
        return $lists;
    }

    /**
     * 获取订单详细
     * @param $id 订单ID
     * @return mixed
     */
    public function getOrderDetailed($id){

        $this->modelOrder->alias('o');

        $join = [
//            [SYS_DB_PREFIX . 'member m','m.id = o.user_id','left'],
            [SYS_DB_PREFIX . 'member_address md','md.id = o.address_id','left'],
            [SYS_DB_PREFIX . 'express e','e.id = o.express_com_id','left'],
        ];

        $where['o.id'] = $id;
        $field = 'o.id,o.order_sn,o.create_time,o.cancelpay_time,o.order_status,o.evaluate_status,o.goods_price,o.real_amount,o.freight,o.discount_price,o.paid_amount,
        IF(LOCATE("http", e.logo) > 0,e.logo,CONCAT("'.Config('setting.site_url').'",e.logo))as express_logo,
        o.pay_time,o.express_sn,o.express_com,o.address_name,o.address_mobile,o.address_detail,md.username,md.mobile,o.refund_status,o.remark_cancel';

        $info = $this->modelOrder->getInfo($where, $field,$join);

        if(!empty($info)){
            $info['order_goods'] = $this->modelOrderGoods
                ->alias('og')
                ->where('og.order_id','eq',$info['id'])
                ->join('goods g','g.id=og.goods_id','left')
                ->field('og.id,og.goods_id,og.goods_name,og.sku_name,og.price,og.buy_nums,og.discount_price,og.old_price,og.express_com,og.return_goods_status
                ,og.express_sn,og.send_time,og.send_status,g.title,g.sub_title,IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb')
                //IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb
                ->select();
        }

        return $info;

    }

    /**
     * 取消订单
     * @param $id订单ID
     * @param $reason 取消原因
     */
    public function cancelOrder($id,$reason,$remark = '用户已取消订单'){
        if(empty($id)){
            return CodeBase::$idIsNull;
        }
        //订单信息
        $orderInfo = Db::name('Order')->where('id','=',$id)->field('id,user_id,order_status,has_option')->find();
        if($orderInfo['order_status'] == 6){
            return OrderError::$orderAlreadyCancel;
        }

        Db::startTrans();
        try{
            $data['order_status'] = 6;   //已取消
            $data['remark_cancel'] = $reason;
            $data['cancel_time'] = time();
            $data['update_time'] = time();

            Db::name('Order')->where('id','=',$id)->update($data);
            //更改商品库存
            $orderGoods = Db::name('OrderGoods')->where('order_id', $id)->field('goods_id,buy_nums,sku_id,user_id,total_cnf')->select();
            foreach ($orderGoods as $k => $v) {
                //has_option0时无商品规格1有商品规格
                if($orderInfo['has_option'] == 1){
                    Db::name('GoodsOption')->where('id', $v['sku_id'])->where('goods_id', $v['goods_id'])->setInc('stock', intval($v['buy_nums']));
                }elseif ($orderInfo['has_option'] == 0){
                    Db::name('Goods')->where('id', $v['goods_id'])->setInc('total', intval($v['buy_nums']));
                }
            }
            insertOrderLog($id,$remark,$orderInfo['user_id'],'',6,getOrderStatusText(6));
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            print_r($e->getMessage());exit;
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 完成订单收货
     * @param $id 订单ID
     */
    public function finishOrder($id){

        $orderData['id'] = $id;
        $orderData['finish_time'] = time();
        $orderData['update_time'] = time();
        $orderData['order_status'] = 4;
        $orderInfo = $this->modelOrder->where('id','=',$id)->field('id,user_id,user_id,order_status,has_option')->find();
        if($orderInfo['order_status'] == 4){
            return OrderError::$orderAlreadyFinish;
        }
        Db::startTrans();
        try{
            $this->modelOrder->where('id','=',$id)->update($orderData);
            //增加销量
//            $this->modelGoods->where('id', $v['goods_id'])->setInc('total', intval($v['buy_nums']));

            //插入订单日志
            insertOrderLog($id,'用户确认收货',$orderInfo['user_id'],'',4,getOrderStatusText(4));
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 删除订单（改变状态假删除）
     * @param $id 订单ID
     */
    public function deleteOrder($id,$user_id){

        $orderData['id'] = $id;
        $orderData['finish_time'] = time();
        $orderData['update_time'] = time();
        $orderData['order_status'] = -1;

        Db::startTrans();
        try{
            $this->modelOrder->where('id','=',$id)->update($orderData);

            //插入订单日志
            insertOrderLog($id,'用户删除订单',$user_id,'',-1,getOrderStatusText(-1));
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 提醒订单发货
     * @param $id 订单ID
     * @param $id 订单号
     */
    public function remindSendGoods($user_id,$id,$order_sn){

            //查询订单已提醒次数
            $where['order_id'] = $id;
            $res = $this->modelRemindSendGoods->where($where)->whereTime('create_time','d')->field('id')->select();
            //今日次数大于3次时提示
            if(count($res) >= 3){
                return OrderError::$remindNumTopLimit;
            }
            $data['user_id'] = $user_id;
            $data['order_id'] = $id;
            $data['order_sn'] = $order_sn;
            $data['create_time'] = time();

            $res =  $this->modelRemindSendGoods->insert($data);

            //插入订单日志
            insertOrderLog($id,'用户提醒订单发货',$user_id);
            if(!$res){
                return CodeBase::$failure;
            }
            return CodeBase::$success;
    }

    /**
     * 查看物流
     * @param $express_sn 运单号
     * @return mixed
     */
    public function getDeliveryInfo($express_sn){
        Loader::import('delivery.Delivery', EXTEND_PATH);
        $deliveryClass = new \Delivery();
        $deliveryInfo = $deliveryClass->getDeliveryInfo($express_sn);

        return $deliveryInfo;

    }
    /**
     * 订单支付
     */
    public function orderPay($user_id,$order_sn,$type='wxapp',$from_type){

        if (!$order_sn || empty($order_sn)) {
            return OrderError::$orderSnError;
        }

        //用户信息
        $user_info = $this->modelMember->getInfo(['id' => $user_id],'id,openid,openid_mini,mobile');

        //订单信息
        $order_info = $this->modelOrder->getInfo(['order_sn' => $order_sn],'id,order_status,order_sn,real_amount');

        if(empty($order_info) || $order_info['order_status'] != 1){
            return OrderError::$orderInfoError;
        }
        $Wxpay = new Wxpay();

        if($type == 'wxapp'){
            $order['body'] = '嘿享购电商';
            $order['out_trade_no'] = $order_info['order_sn'];
            $order['total_fee'] = $order_info['real_amount'];
            $order['trade_type'] = 'JSAPI';
            $order['from_type'] = $from_type;
            $order['user_id'] = $user_info['id'];
            $order['openid'] = $from_type == 1 ? $user_info['openid'] : $user_info['openid_mini']; //1是公众号 ，2是小程序
            $order['attach'] = 'order';
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
     * 发票列表
     * @param $user_id 会员ID
     */
    public function invoiceList($user_id){

        $where['user_id'] = $user_id;

        $field = 'id,order_id,order_sn,invoice_type,invoice_rise,rise_content,invoice_amount,
        IF(status = 2,"暂未开票",operation_time) as operation_time,status';

        $lists =  $this->modelOrderInvoice->getList($where, $field, 'create_time desc', 5);

        return $lists;
    }

    /**
     * 获取订单取消原因文字
     * @param $user_id 会员ID
     */
    public function getOrderReasonText($type){

        $where['status'] = 1;

        $where['type'] = $type;

        $field = 'id,content,status';

        $lists =  $this->modelOrderReasonText->getList($where, $field, 'sort desc', false);

        return $lists;
    }

    /**
     * 获取评论列表
     * @param $user_id 会员ID
     */
    public function getEvaluateList($user_id){

//        $where['o.user_id'] = $user_id;
        $where['o.user_id'] = $user_id;
        $where['o.order_status'] = 4;
        $where['og.evaluate_status'] = 0;

        $this->modelOrderGoods->alias('og');

        $join = [
            [SYS_DB_PREFIX . 'order o','o.id = og.order_id','left'],
            [SYS_DB_PREFIX . 'goods g','g.id = og.goods_id','left'],
        ];

        $field = 'og.id,o.id as order_id,o.order_sn,o.real_amount,o.freight,o.order_status,og.goods_id,og.create_time,og.real_price,og.buy_nums,g.title,g.sub_title,
        IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb,og.sku_name';

        $lists =  $this->modelOrderGoods->getList($where, $field, 'og.create_time desc', 6,$join);

        if(!empty($lists)){
            foreach ($lists as $key => $val){
                $arr = [];
                $arr[0]['order_id'] = $val['order_id'];
                $arr[0]['goods_id'] = $val['goods_id'];
                $arr[0]['goods_name'] = $val['title'];
                $arr[0]['title'] = $val['sub_title'];
                $arr[0]['sub_title'] = $val['title'];
                $arr[0]['price'] = $val['real_price'];
                $arr[0]['buy_nums'] = $val['buy_nums'];
                $arr[0]['sku_name'] = $val['sku_name'];
                $arr[0]['thumb'] = $val['thumb'];
                $lists[$key]['order_goods'] = $arr;
                $lists[$key]['order_num'] = 1;
            }
        }
        return $lists;

    }

    /**
     * 批量取消订单
     */
    public  function batchCancelOrder()
    {
        //获取全局配置订单取消时间
//        $order_time = Db::name('Config')->where('name', 'eq', 'cancel_order_time')->value('value');
        $where = [];
        $where['order_status'] = 1;
        $res = Db::name('Order')->where($where)->field('id,order_sn,create_time,cancelpay_time')->select();
        if ($res) {
            foreach ($res as $k => $v) {
                //如果当前时间大于取消付款时间则取消订单
                if(time() > $v['cancelpay_time']){
                    $this->cancelOrder($v['id'],  '卖家未付完款自动取消订单', '卖家未付完款自动取消订单');
                }
            }
        }
    }

    /**
     * 批量自动完成订单收货
     */
    public function batchAutoFinishOrder(){
        //获取全局配置订单取消时间
        $finish_time = Db::name('Config')->where('name', 'eq', 'finish_order_time')->value('value');

        $where = [];
        $where['order_status'] = 3;
        $where['send_time'] = ['lt', time() - $finish_time * 24 * 60 * 60];
        $res = Db::name('Order')->where($where)->field('id,order_sn,create_time')->select();
        if ($res) {
            foreach ($res as $k => $v) {
                $this->finishOrder($v['id'], $finish_time . '天内未确认收货，系统自动收货',$finish_time . '天内未确认收货，系统自动收货');
            }
        }

    }

    /**
     * 订单退换货
     * @param $user_id 会员ID
     * @param $param 数据
     */
    public function returnOrder($user_id,$param){
        //1.type (0.仅退款 1.退货退款)

        $validate_result = $this->validateReturnOrder->scene('return_type')->check($param);
        if (!$validate_result) {
            return OrderError::usernameOrPasswordEmpty(6050006,$this->validateReturnOrder->getError());
        }

        Db::startTrans();
        try{
            if($param['type'] == 0) {
                //数据验证
                $validate_result = $this->validateReturnOrder->scene('return_money')->check($param);
                if (!$validate_result) {
                    return OrderError::usernameOrPasswordEmpty(6050006,$this->validateReturnOrder->getError());
                }
                $data['goods_status'] = $param['goods_status'];

            }elseif ($param['type'] == 1){
                //数据验证
                $validate_result = $this->validateReturnOrder->scene('return_goods')->check($param);
                if (!$validate_result) {
                    return OrderError::usernameOrPasswordEmpty(6050006,$this->validateReturnOrder->getError());
                }
            }
            $order_goods = $this->modelOrderGoods->where(['id'=>$param['rec_id']])->field('price,buy_nums,order_id,goods_id')->find();
            $useRapplyReturnMoney = $order_goods['price'] * $order_goods['buy_nums'];    //可退的总价 商品购买单价*购买数量
            if($param['refund_money'] > $useRapplyReturnMoney){
                return OrderError::$returnMoney;
            }
            $data['refund_money'] = $param['refund_money'];  //用户申请退款金额
            $data['reason'] = $param['reason'];  //原因
            $data['user_id'] = $user_id;
//            $data['order_sn'] = $param['order_sn'];
            $data['rec_id'] = $param['rec_id'];
            $data['order_id'] = $order_goods['order_id'];
            $data['goods_id'] = $order_goods['goods_id'];
            $data['describe'] = $param['describe']; //备注描述
            $data['add_time'] = time();
            $this->modelReturnGoods->insert($data);
            //修改订单商品状态
            $this->modelOrderGoods->where('id',$param['rec_id'])->update(['return_goods_status' => 1]);

            Db::commit();
        }catch (Exception $e){
            halt($e->getMessage());
                    Db::rollback();
            return CodeBase::$failure;
        }
            return CodeBase::$success;
    }

    /**
     * 退换货订单列表
     * $user_id 会员ID
     */
    public function returnOrderList($user_id){

        $where = [];
        $where['rg.user_id'] = $user_id;

        $this->modelReturnGoods->alias('rg');

        $join = [
            [SYS_DB_PREFIX . 'order_goods og','og.id = rg.rec_id','left'],
            [SYS_DB_PREFIX . 'goods g','g.id = rg.goods_id','left'],
            [SYS_DB_PREFIX . 'order o','o.id = rg.order_id','left'],
        ];


        $field = 'rg.id,o.order_sn,rg.add_time,rg.status,g.title,g.sub_title,og.sku_name,og.buy_nums,rg.refund_money,rg.type,
        IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb';
        $lists =  $this->modelReturnGoods->getList($where, $field, 'rg.add_time desc', 6,$join);

        return $lists;
    }

    /**
     * 退换货订单详细
     * @param $id 退换单ID
     */
    public function returnOrderDetailed($id){
        if(empty($id)){
            return CodeBase::$idIsNull;
        }

        $this->modelReturnGoods->alias('rg');

        $join = [
            [SYS_DB_PREFIX . 'order_goods og','og.id = rg.rec_id','left'],
            [SYS_DB_PREFIX . 'goods g','g.id = rg.goods_id','left'],
            [SYS_DB_PREFIX . 'order o','o.id = rg.order_id','left'],
        ];

        $where['rg.id'] = $id;

        $field = 'rg.id,o.order_sn,rg.add_time,rg.status,rg.reason,rg.refund_time,g.title,g.sub_title,og.sku_name,og.buy_nums,rg.refund_money,rg.type,
        og.real_price,IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb';

        $info = $this->modelReturnGoods->getInfo($where, $field,$join);

        if(empty($info)){
            $info = [];
        }
        return $info;

    }

    /**
     * 申请订单退款
     * @param $user_id 会员ID
     * @param $id 订单ID
     * @param $id 退款原因
     */
    public function applyOrderRefund($user_id,$id,$reason){
        if(empty($id)){
            CodeBase::$idIsNull;
        }

        Db::startTrans();
        try{
            //TODO 商家同意申请后还库存
//            $this->cancelOrder($id,'用户申请退款');
            $this->modelOrder->where('id',$id)->update(['refund_status' => 1]);
            $orderInfo = $this->modelOrder->where('id',$id)->field('id,order_sn,paid_amount')->find();
            if (empty($orderInfo)) {
                return OrderError::$orderInfoError;
            }
            $data['order_id'] = $orderInfo['id'];
            $data['order_sn'] = $orderInfo['order_sn'];
            $data['create_time'] = time();
            $data['user_id'] = $user_id;
            $data['reason'] = $reason;
            $data['refund_amount'] = $orderInfo['paid_amount'];
            $data['status'] = 1;
            $this->modelOrderRefund->insert($data);
            Db::commit();
        }catch (Exception $e){

            halt($e->getMessage());
            Db::rollback();
            return CodeBase::$failure;
        }

        return CodeBase::$success;
    }

    /**
     * 查询物流公司列表
     * @return mixed
     */
    public function expressList(){
        $where['status'] = 1;

        $field = 'id,name,sort,status';

        $lists =  $this->modelExpress->getList($where, $field, 'sort asc', false);

        return $lists;
    }

    /**
     * 填写物流公司信息
     * @param $param
     */
    public function setDelivery($param){

        if(!$this->validateSetDelivery->scene('delivery')->check($param))
        {
            return OrderError::usernameOrPasswordEmpty('1070002',$this->validateSetDelivery->getError());
        }

        $serialize['express_name'] = $param['express_name'];
        $serialize['express_sn'] = $param['express_sn'];
        //数据组装
        $data['delivery'] = serialize($serialize);
        $data['describe'] = $param['describe'];
        $data['moblie'] = $param['moblie'];
        $data['status'] = 2;

        $result = $this->modelReturnGoods->where('id',$param['id'])->update($data);

        if(!$result){
            return CodeBase::$failure;
        }

        return CodeBase::$success;
    }

}






