<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: chenhg <945076855@qq.com>
 * Date: 2018/6/27 23:16
 */

namespace app\api\logic;

use think\Cache;
use think\Db;
use think\Exception;
use app\api\error\Checkout as CheckoutError;
use think\Session;

/**
 * 结算逻辑
 * @author chenhg <945076855@qq.com>
 * @package app\api\logic
 */
class Checkout extends ApiBase
{


    protected $userid = 0;
    protected $goodsList = null; // 商品列表
    protected $buyGoodsList = null; // 用户购买的商品
    protected $userCouponNumArr; //用户符合购物车店铺可用优惠券数量

    // 用户在选择商品后，向ＡＰＩ提交包含它所选择商品的相关信息
    // API在接收到信息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库中= 下单成功了，返回客户端消息，告诉客户端可以支付了
    // 调用我们的支付接口，进行支付
    // 还需要再次进行库存量检测
    // 服务器这边就可以调用微信的支付接口进行支付
    // 小程序根据服务器返回的结果拉起微信支付
    // 微信会返回给我们一个支付的结果（异步）
    // 成功：也需要进行库存量的检查
    // 成功：进行库存量的扣除

    // 做一次库存量检测
    // 创建订单
    // 减库存--预扣除库存
    // if pay 真正的减库存
    // 在一定时间内30min没有去支付这个订单，我们需要还原库存

    /**
     * 生成下单数据
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param string $action buynow：立即购买 / checkout 购物车结算
     * @param $ids  buynow: $ids 为商品ID ；checkout：$ids为购物车ID
     * @param int $userid 用户ID
     * @param int $number 数量 购物车情况可以为空
     * @param int $sku_id 立即购买可以传 sku id
     * @param int $address_id收货地址id
     */
    public function placeOrder($action = 'buynow',$data = [],$user_id)// $ids, $userid,$address_id, $number = 0, $sku_id = 0
    {
        if(empty($data['goods_id']) && empty($data['ids'])) return CheckoutError::$idIsNull;

        $this->userid = $user_id;

        $address_id = $this->modelMemberAddress->getValue(['user_id'=>$this->userid,'is_default'=>1,'status'=>1],'id');

        $result = [];
        switch ($action) {
            case 'buynow':
                !isset($data['sku_id']) && $data['sku_id'] = 0;
                !isset($data['number']) && $data['number'] = 1;
                $result = $this->getBuynowList($data['goods_id'], $data['sku_id'], $data['number'],$address_id);
                break;
            case "checkout":
                $result = $this->getCheckList($data['ids'],$address_id);
                $result['ids'] = $data['ids'];
                break;
            default:
                break;
        }
        if(isset($result['code'])) return $result;

        $cartGoodsId = get_arr_column($result['pStatusArray'],'goods_id');
        $cartGoodsCatId = get_arr_column($result['pStatusArray'],'cates');

        //用户可用的优惠券列表
        $userCouponList = $this->getUserAbleCouponList($user_id,$cartGoodsId,$cartGoodsCatId);
        //优惠券，用able判断是否可用
        $result['couponList'] = $this->getCouponCartList($result, $userCouponList);

        $result['action'] = $action;
        $result['orderAmount'] = $result['goodsAmount'] + $result['freight'];

        Cache::set(md5($result['order_sn']),$result);

        return $result;
    }

    /**
     * getCheckList
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $ids
     * @param $address_id
     * @return array|bool|mixed|null
     */
    public function getCheckList($ids,$address_id)
    {
        // 获取商品信息
        $this->goodsList = $this->buyGoodsList = $this->getCartList($ids);

        if(isset($this->goodsList['code']))  return $this->goodsList;
        $check = $this->checkBuyNumber();
        if(isset($check['code']))  return $check;

        // 组成订单信息 用户默认地址
        $orderInfo = $this->getCheckOrderStatus($address_id);

        //运费信息
        $sendInfo = $this->getSendFee($orderInfo,$address_id);

        return $sendInfo;
    }

    /**
     * 转换购物车的优惠券数据
     * @param $cartList|购物车商品
     * @param $userCouponList|用户优惠券列表
     * @return mixedable
     */
    public function getCouponCartList($cartList, $userCouponList){

        $userCouponArray = collection($userCouponList)->toArray();  //用户的优惠券
        $couponNewList = [];
        $coupon_num=0;

        foreach ($userCouponArray as $couponKey => $couponItem) {
            if ($userCouponArray[$couponKey]['coupon']['use_type'] == 0) { //全店使用优惠券
                if ($cartList['goodsAmount'] >= $userCouponArray[$couponKey]['coupon']['enough']) {  //订单商品总价是否符合优惠券购买价格
                    $userCouponArray[$couponKey]['coupon']['able'] = 1;

                    //判断
                    $this->isAble($couponItem);
                    if($couponItem['coupon']['time_limit'] == 2 && $couponItem['end_time'] > 0 && $couponItem['end_time'] < date('Y-m-d',time())){
                        $userCouponArray[$couponKey]['coupon']['able'] = 0;
                    }
                    $coupon_num +=1;
                } else {
                    $userCouponArray[$couponKey]['coupon']['able'] = 0;
                }
            } elseif ($userCouponArray[$couponKey]['coupon']['use_type'] == 1) { //指定商品优惠券
                $pointGoodsPrice = 0;//指定商品的购买总价
                $couponGoodsId = get_arr_column($userCouponArray[$couponKey]['coupon']['goods_coupon'], 'goods_id');
                //循环购物车商品
                foreach ($cartList['pStatusArray'] as $tKey => $Item) {
                    if (in_array($Item['goods_id'], $couponGoodsId)) {
                        $pointGoodsPrice += $Item['price'] * $Item['count'];  //用会员折扣价统计每个商品的总价
                    }
                }
                if ($pointGoodsPrice >= $userCouponArray[$couponKey]['coupon']['enough']) {
                    $userCouponArray[$couponKey]['coupon']['able'] = 1;

                    if($couponItem['end_time'] > 0 && $couponItem['end_time'] < date('Y-m-d',time())){
                        $userCouponArray[$couponKey]['coupon']['able'] = 0;
                    }
                    $coupon_num +=1;
                } else {
                    $userCouponArray[$couponKey]['coupon']['able'] = 0;
                }
            } elseif ($userCouponArray[$couponKey]['coupon']['use_type'] == 2) { //指定商品分类优惠券
                $pointGoodsCatPrice = 0;//指定商品分类的购买总价
                $couponGoodsCatId = get_arr_column($userCouponArray[$couponKey]['coupon']['goods_coupon'], 'goods_category_id');
                foreach ($cartList['pStatusArray'] as $tKey => $Item) {

                    if (in_array($Item['cates'], $couponGoodsCatId)) {
                        $pointGoodsCatPrice += $Item['price'] * $Item['count']; //用会员折扣价统计每个商品的总价
                    }
                }
                if ($pointGoodsCatPrice >= $userCouponArray[$couponKey]['coupon']['enough']) {
                    $userCouponArray[$couponKey]['coupon']['able'] = 1;

                    if($couponItem['end_time'] > 0 && $couponItem['end_time'] < date('Y-m-d',time())){
                        $userCouponArray[$couponKey]['coupon']['able'] = 0;
                    }
                    $coupon_num +=1;
                } else {
                    $userCouponArray[$couponKey]['coupon']['able'] = 0;
                }
            } else {
                $userCouponList[$couponKey]['coupon']['able'] = 1;
            }
            $couponNewList[] = $userCouponArray[$couponKey];
        }
        $this->userCouponNumArr['usable_num'] = $coupon_num;
        return $couponNewList;
    }

    public function isAble($couponItem){

    }


    /**
     * 获取优惠券列表
     * @param $user_id 用户ID
     * @param $result 数据
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getUserAbleCouponList($user_id,$goods_ids = [], $goods_cat_id = []){


        $userCouponArr = [];
        $where['user_id'] = $user_id;
        $where['used'] = 1;
        //用户优惠券
//        $userCouponList = $this->modelCouponData->getList($where,'id','get_time desc',false);
        $userCouponList = Db::name('CouponData')->where($where)->select();

        if(!$userCouponList){
            return $userCouponArr;
        }
        $userCouponId = get_arr_column($userCouponList, 'coupon_id');
        $couponList = $this->modelCoupon->with('GoodsCoupon')
            ->where('id', 'in', $userCouponId)
            ->where('status' ,1)
            ->where('time_start', '<', time())
            ->where('time_end', '>', time())
            ->select();//检查优惠券是否可以用

        foreach ($userCouponList as $userCoupon => $userCouponItem) {
            $userCouponItem['end_time'] = $userCouponItem['end_time'] > 0 ? date('Y-m-d',$userCouponItem['end_time']) : 0;
            foreach ($couponList as $coupon => $couponItem) {

                if($userCouponItem['coupon_id'] == $couponItem['id']){
                    //全店通用
                    if ($couponItem['use_type'] == 0) {
                        $tmp = $userCouponItem;
                        $tmp['coupon'] = $couponItem->append(['use_type_title'])->toArray();
                        $userCouponArr[] = $tmp;
                    }
                    //限定商品
                    if ($couponItem['use_type'] == 1 && !empty($couponItem['goods_coupon'])) {
                        foreach ($couponItem['goods_coupon'] as $goodsCoupon => $goodsCouponItem) {
                            if (in_array($goodsCouponItem['goods_id'], $goods_ids)) {
                                $tmp = $userCouponItem;
                                $tmp['coupon'] = array_merge($couponItem->append(['use_type_title'])->toArray(), $goodsCouponItem->toArray());
                                $userCouponArr[] = $tmp;
                                break;
                            }
                        }
                    }
                    //限定商品类型
                    if ($couponItem['use_type'] == 2 && !empty($couponItem['goods_coupon'])) {
                        foreach ($couponItem['goods_coupon'] as $goodsCoupon => $goodsCouponItem) {
                            if (in_array($goodsCouponItem['goods_category_id'], $goods_cat_id)) {
                                $tmp = $userCouponItem;
                                $tmp['coupon'] = array_merge($couponItem->append(['use_type_title'])->toArray(), $goodsCouponItem->toArray());
                                $userCouponArr[] = $tmp;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $userCouponArr;

    }


    public function getUserAbleCouponList111($user_id,$goods_ids = [], $goods_cat_id = []){


        $this->modelCouponData->alias('cd');
        $userCouponArr = [];
        $where['user_id'] = $user_id;
        $join = [
            ['coupon c','c.id=cd.coupon_id','left'],
        ];
        $field = ['c.id,cd.coupon_id,c.coupon_name,c.enough,c.time_limit,c.time_days,c.time_start,
        c.time_end,cd.end_time,c.discount,c.deduct,c.back_type,c.title_color,cd.used,
        c.limit_good_type,c.limit_good_cate_type,c.limit_good_cateids,c.limit_good_ids'];
        //用户优惠券
        $userCouponList = $this->modelCouponData->getList($where,$field,'cd.get_time desc',false,$join);

        if(!$userCouponList){
            return $userCouponArr;
        }
        $userCouponId = get_arr_column($userCouponList, 'coupon_id');

        return $userCouponArr;

    }

    /**
     * 删除购物车信息
     * @ids 购物车id数组
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function deleteCheckOut($ids)
    {
        if(!empty($ids))
        {
            return $this->modelMemberCart->setInfo(['status'=>-1],['id'=>['in',explode(',',$ids)]]);
        }
        return false;
    }
    private function checkBuyNumber()
    {
        if(!empty($this->goodsList) && is_array($this->goodsList))
        {
            foreach ($this->goodsList as $value)
            {
                if(!empty($value['max_buy']) && ($value['max_buy'] < $value['count'])) return CheckoutError::$outMaxBuyNumber;
                $all_buy_goods = $this->modelOrderGoods->getBuyGoods(['o.user_id'=>$this->userid,'og.goods_id'=>$value['goods_id'],'o.order_status'=>4,'o.refund_status'=>0,'o.pay_type'=>['>',0]],'og.buy_nums');

                if(!empty($value['user_max_buy']) && (($all_buy_goods + $value['count']) > $value['user_max_buy'])) return CheckoutError::$outAllMaxBuyNumber;
            }
        }
        return true;
    }

    /**
     * getBuynowInfo
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param $goods_id
     * @param $sku_id
     * @param $number
     */
    public function getBuynowList($goods_id, $sku_id, $number,$address_id = 0)
    {
        // 获取商品信息
        $this->goodsList = $this->buyGoodsList = $this->getGoodsList('buynow', $goods_id, $sku_id, $number);
        if(isset( $this->goodsList['code']))  return  $this->goodsList;
        $check = $this->checkBuyNumber();

        if(isset($check['code']))  return $check;

        // 检查商品状态 检查商品库存
        $pStatus = $this->getCheckGoodsStatus($goods_id, $number, $this->goodsList);

        // 组成订单信息 用户默认地址
        $orderInfo = $this->getCheckOrderStatus($address_id);
        //运费信息
        $sendInfo = $this->getSendFee($orderInfo,$address_id);

        return $sendInfo;
    }

    public function getCartList($cartids)
    {
        return $this->getGoodsList('cart', $cartids);
    }

    /**
     *
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param string $action
     * @param $ids
     * @param $sku_id
     */
    public function getGoodsList($action = 'cart', $ids, $sku_id = 0, $number = 0)
    {
        $goodslist = [];

        if ($action == 'cart') {
            $cartList = $this->modelMemberCart->getCartList($ids);

            foreach ($cartList as $cart) {
                $goodsInfo = $this->getGoodsInfo($cart['goods_id'], $cart['sku_id']);
                if(empty($goodsInfo))
                {
                    return CheckoutError::createError('1000005','购物车信息有误');
                }

                $goodsInfo['count'] = $cart['num'];
                $goodslist[] = $goodsInfo;
            }
        } else {
            $goodsInfo = $this->getGoodsInfo($ids, $sku_id);

            $goodsInfo['count'] = $number;
            $goodslist[] = $goodsInfo;
        }
        return $goodslist;
    }


    public function getGoodsInfo($goods_id, $sku_id = 0)
    {
        if ($sku_id == 0) { // 没有规格情况
            $goodsInfo = $this->modelGoods->getGoodsInfo($goods_id);
        } else {
            $goodsInfo = $this->modelGoods->getGoodsSkuInfo($goods_id, $sku_id);
        }
        return $goodsInfo;
    }

    /**
     * 检查产品信息状态
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param $goods_id
     * @param $number
     * @param $goodsInfo
     * @return array
     */
    private function getCheckGoodsStatus($goods_id, $number, $goodsList)
    {
        $pIndex = -1;
        $pStatus = [
            'goods_id' => null,
            'haveStock' => false,
            'count' => 0,
            'price' => 0,
            'name' => '',
            'totalPrice' => 0,
            'goods_image' => null,
            'weight' => 0,
            'sku_id' => 0,
            'sku_name' => ''
        ];
        // 购买的产品是否存在
        foreach ($goodsList as $k => $goodsInfo) {
            if ($goods_id == $goodsInfo['goods_id']) {
                $pIndex = $k;
            }
        }
        if ($pIndex == -1) {
            // 客户端传递的goods_id有可能根本不存在
            return $pStatus;
        }
        $goodsInfo = $goodsList[$pIndex];

        // 订单产品基本信息
        $pStatus['goods_id'] = $goodsInfo['goods_id'];
        $pStatus['cates'] = $goodsInfo['cates'];
        $pStatus['name'] = $goodsInfo['name'];
        $pStatus['count'] = $number;
        $pStatus['price'] = $goodsInfo['price'];
        $pStatus['goods_image'] = $goodsInfo['goods_image'];
        $pStatus['totalPrice'] = $goodsInfo['price'] * $number;
        $pStatus['weight'] = $number * $goodsInfo['weight'];
        $pStatus['sku_id'] = $goodsInfo['sku_id'];
        $pStatus['sku_name'] = $goodsInfo['sku_name'];
        $pStatus['total_cnf'] = $goodsInfo['total_cnf'];
        $pStatus['dispatch_type'] = $goodsInfo['dispatch_type'];
        $pStatus['dispatch_id'] = $goodsInfo['dispatch_id'];
        $pStatus['is_dispatch_price'] = $goodsInfo['is_dispatch_price'];
        $pStatus['dispatch_price'] = $goodsInfo['dispatch_price'];
        $pStatus['is_send_free'] = $goodsInfo['is_send_free'];

        if($goodsInfo['stock'] != -1)
        {
            if ($goodsInfo['stock'] - $number >= 0) {
                $pStatus['haveStock'] = true;
            }
        }else
        {
            $pStatus['haveStock'] = true;
        }

        return $pStatus;
    }

    /**
     * 运费
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getSendFee($orderInfo,$address_id = 0,$type = 'default')
    {
        empty($address_id) ? $where['is_default'] = 1 : $where['id'] = $address_id;

        $address = $this->modelMemberAddress->getInfo($where);

        if($type == 'change')
        {
            $orderInfo = Cache::get(md5($orderInfo));
        }

        $send_fee = $this->getSendPrice($address,$orderInfo);

        $orderInfo['freight'] = $send_fee;

        return $orderInfo;
    }

    /**
     * 邮费计算
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function getSendPrice($address,$orderInfo)
    {
        $send_fee = 0;
        $added_goods = [];
        $added_goods_js = [];
        if(!empty($orderInfo['pStatusArray']))
        {
            foreach ($orderInfo['pStatusArray'] as $value)
            {
                if($value['is_send_free'] != 1) //不包邮
                {
                    if($value['is_dispatch_price'] == 1) //该商品统一邮费（不包邮）
                    {
                        if(!in_array($value['goods_id'],$added_goods))
                        {
                            $added_goods[] = $value['goods_id'];
                            $send_fee += $value['dispatch_price'];
                        }
                    }else  //不包邮 and 按件/重量计费
                    {
                        !empty($value['dispatch_id']) ? $dispatch_where['id'] = $value['dispatch_id'] : $dispatch_where['is_default'] = 1;

                        $dispatch = $this->modelExpressDispatch->getInfo($dispatch_where,'*',null,null,true,'default_dispatch');

                        $this->checkSendAddress($dispatch,$address);

                        if(!in_array($value['goods_id'],$added_goods_js))
                        {
                            $added_goods_js[] = $value['goods_id'];

                            if($dispatch['calculate_type'] == 1)//按件计费
                            {
                                $num = $value['count'] - $dispatch['first_num'];
                                $send_fee += ($num > 0) ? $dispatch['first_num_price'] + ceil($num/$dispatch['second_num']) * $dispatch['second_num_price'] :$dispatch['first_num_price'] ;

                            }else//按重量计费
                            {
                                if(!empty($value['weight']))
                                {
                                    $num = $value['count'] * $value['weight'] - $dispatch['first_weight'];
                                    $send_fee += ($num > 0) ? $dispatch['first_price'] + ceil($num/$dispatch['second_weight']) * $dispatch['second_price'] :$dispatch['first_price'] ;
                                }

                            }
                        }else
                        {
                            if($dispatch['calculate_type'] == 1)//按件计费
                            {
                                $send_fee += ceil($value['count']/$dispatch['second_num']) * $dispatch['second_num_price'];

                            }else//按重量计费
                            {
                                if(!empty($value['weight']))
                                {
                                    $send_fee += ceil(($value['count'] * $value['weight'])/$dispatch['second_weight']) * $dispatch['second_price'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $send_fee;
    }

    /**
     * 判断收货地址是否在配送区域内
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function checkSendAddress($dispatch,$address)
    {
        if(!empty($dispatch['nodispatchareas_code']))
        {
            $address_lists = explode(';',unserialize($dispatch['nodispatchareas_code']));

            if($dispatch['is_dispatcharea'] == 1 && !in_array($address['province']['province'].' '.$address['city']['city'].' '.$address['county']['county'],$address_lists))//只配送区域
            {
                return CheckoutError::$notDispatch;
            }
            if($dispatch['is_dispatcharea'] != 1 && in_array($address['province']['province'].' '.$address['city']['city'].' '.$address['county']['county'],$address_lists))//只配送区域
            {
                return CheckoutError::$notDispatch;
            }
        }
        return true;
    }
    /**
     * 获取订单状态
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @return array
     */
    private function getCheckOrderStatus($address_id = 0)
    {
        $status = [
            'pass' => true,
            'goodsAmount' => 0,  // 订单总价
            'orderAmount' => 0,  // 订单总价
            'totalCount' => 0,  // 订单总数量
            'totalWeight' => 0, //订单总重量
            'pStatusArray' => [],    // 订单商品
            'order_sn'=>makeOrderNo(),
            'freight' => 0,//运费
            'address_id' => $address_id,
            'discountAmount' => 0
        ];
        foreach ($this->goodsList as $oProduct) {
            $pStatus = $this->getCheckGoodsStatus(
                $oProduct['goods_id'], $oProduct['count'], $this->buyGoodsList
            );
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['goodsAmount'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            $status['totalWeight'] += $pStatus['weight'];

            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }


    /**
     * preOrderData
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function preOrderData($order_info,$userid,$orderPrice, $address_id,$remark)
    {
        $data['order_sn'] = makeOrderNo();
        $data['user_id'] = $userid;
        $data['real_amount'] = $order_info['orderAmount'];
        $data['paid_amount'] = '';
        $data['freight'] = $order_info['freight'];
        $data['order_status'] = 1;
        $data['price'] = $order_info['orderAmount'];
        $data['remark'] = $remark;
        $data['address_id'] = $address_id;

        if(!empty($order_info['pStatusArray']))
        {
            foreach ($order_info['pStatusArray'] as $k=>$value)
            {
                $order_goods[$k] = [
                    'goods_id' => $value['goods_id'],
                    'order_id' => '',
                    'price' => $value['price'],
                    'real_price' => $value['totalPrice'],
                    'total_cnf' => $value['total_cnf'],
                    'dispatch_type' => $value['dispatch_type'],
                    'dispatch_id' => $value['dispatch_id'],
                    'is_dispatch_price' => $value['is_dispatch_price'],
                    'dispatch_price' => $value['dispatch_price'],
                    'is_send_free' => $value['is_send_free'],
                    'buy_nums' => $value['count'],
                    'total_price' => $value['totalPrice'],
                    'sku_id' => $value['sku_id'],
                    'sku_name' => $value['sku_name'],
                    'goods_name' => $value['name'],
                    'create_time' => time(),
                ];
            }
        }
        $data['order_goods'] = $order_goods;
        return $data;
    }

    /**
     * 创建订单
     * @orderPrice 订单价格
     * @address_id 收获地址
     * @order_sn 临时订单号
     * @remark 买家备注
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function createOrder($userid,$data = [])
    {
        if(empty($data['order_sn'])) return CheckoutError::createError('1000005','订单号不能为空');
        //获取订单信息
        $order_info = Cache::get(md5($data['order_sn']));

        if(empty($order_info)) return CheckoutError::$orderInfoNull;

        //检查是否重复下单
        if($this->modelOrder->getInfo(['temp_order_sn'=>$data['order_sn']]))
            return CheckoutError::$notRepeatCreateOrder;

        // 再次检查库存
        if(!empty($order_info['pStatusArray']))
        {
            foreach ($order_info['pStatusArray'] as $k=>$value)
            {
                $sku = $this->getCheckGoodsStatus($value['goods_id'],$value['count'],[0=>$this->getGoodsInfo($value['goods_id'],$value['sku_id'])]);
            }
        }
        // 创建订单
        Db::startTrans();
        try {

            $add_order = $this->preOrderData($order_info,$userid,$data['orderPrice'], $data['address_id'],$data['remark']);

            //使用优惠券
            if(!empty($data['coupon_id'])){
            //优惠价格
                $couponPrice = $this->useCouponById($userid,$data['coupon_id'],$order_info);
                $add_order['real_amount'] = $couponPrice['realAmount'];
                $add_order['discount_price'] = $couponPrice['couponPrice'];
            }

            $order_goods = $this->tidyOrder($add_order,$data);//添加订单主表

            $this->modelOrderGoods->insertAll($order_goods['order_goods']);
            if(!empty($data['invoice']))
            {
                $data['real_amount'] = $add_order['real_amount'];
                $data['order_sn'] = $add_order['order_sn'];
                $this->addInvoice($data,$order_goods['order_id'],$userid);
            }
            insertOrderLog($order_goods['order_id'],'用户下单',$userid,'',1,getOrderStatusText(1));

            if(!empty($data['coupon_id'])){
                insertOrderLog($order_goods['order_id'],'使用优惠券:'.$couponPrice['coupon_name'].',优惠金额:'.$couponPrice['couponPrice'],$userid,'',1,getOrderStatusText(1));
                //修改优惠券状态已使用
                $couponData['used'] = 2;
                $couponData['use_time'] = time();
                $this->modelCouponData->where(['user_id'=>$userid,'id'=>$data['coupon_id']])->update($couponData);
            }
            if($order_info['action'] == 'checkout')
            {
                //删除购物车信息
                $this->deleteCheckOut($order_info['ids']);
            }

            Db::commit();
            return ['order_sn'=>$add_order['order_sn'],'cancelpay_time'=>$order_goods['cancelpay_time']];
        } catch (Exception $exception)
        {
            Db::rollback();
            return CheckoutError::createError('1000005',$exception->getMessage());
        }

    }

    /**
     * 使用优惠券
     * @param $userid 用户ID
     * @param $coupon_id 优惠券ID
     */
    public function useCouponById($userid,$coupon_id,$order_info){
        if($coupon_id > 0){
            $userCoupon = $this->modelCouponData->where(['user_id'=>$userid,'id'=>$coupon_id,'used'=>1])->find();
            if($userCoupon){
                $coupon = Db::name('coupon')->where(['id'=>$userCoupon['coupon_id'],'status'=>1])->find(); // 获取有效优惠券类型表
                if($coupon){
                    $return['couponId'] = $coupon_id;

                    //优惠方式（1：立减 2：打折 ）
                    if($coupon['back_type'] == 1){
                        $return['couponPrice'] = $coupon['deduct'];
                        $return['realAmount'] =  sprintf("%.2f",$order_info['goodsAmount'] - $coupon['deduct']);
                        $return['coupon_name'] = $coupon['coupon_name'];
                    }elseif ($coupon['back_type'] == 2){
                        $return['couponPrice'] = sprintf("%.2f",$order_info['goodsAmount'] * (1 - $coupon['discount']/10));
                        $return['realAmount'] =  sprintf("%.2f",substr(sprintf("%.3f", ($order_info['goodsAmount'] * ($coupon['discount']/10))), 0, -2));
                        $return['coupon_name'] = $coupon['coupon_name'];
                    }
                }
            }
            return $return;
        }
    }

    /**
     * 添加发票信息
     * @add_order 订单信息
     * @order_id 订单id
     * @userid 用户id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function addInvoice($add_order,$order_id,$userid)
    {
        return $this->modelOrderInvoice->setInfo(['order_sn'=>$add_order['order_sn'],'order_id'=>$order_id,'user_id'=>$userid,'invoice_type'=>$add_order['invoice'],
            'invoice_rise'=>$add_order['invoice_type'],'rise_content'=>$add_order['invoice_raised'],'invoice_amount'=>$add_order['real_amount'],'status'=>2,'create_time'=>time()]);
    }

    /**
     * 订单商品数组中添加订单id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function addOrderId($order_goods,$order_id)
    {
        foreach ($order_goods as $k=>$value)
        {
            $order_goods[$k]['order_id'] = $order_id;
        }
        return $order_goods;
    }

    /**
     * 整理获取到的数组信息并添加数据库
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function tidyOrder($add_order,$data)
    {
        $order_goods = array_chunk($add_order,'9')[1][0];//$add_order['order_goods'];

        unset($add_order['order_goods']);

        $add_order['invoice'] = $data['invoice'];
        $add_order['invoice_type'] = $data['invoice_type'];
        $add_order['invoice_raised'] = $data['invoice_raised'];
        $add_order['invoice_tax'] = $data['invoice_tax'];
        $add_order['invoice_email'] = $data['invoice_email'];
        $add_order['create_time'] = time();
        $add_order['cancelpay_time'] = strtotime("+30 minute");
        $add_order['temp_order_sn'] = $data['order_sn'];

        //查询地址信息
        $addressInfo = $this->modelMemberAddress->getInfo(['id' => $data['address_id']],'*');
        $add_order['address_name'] = $addressInfo['username'];
        $add_order['address_mobile'] = $addressInfo['mobile'];

        $add_order['address_detail'] = getCityName($addressInfo['province']['province']).getCityName($addressInfo['city']['city']).getCityName($addressInfo['county']['county']).$addressInfo['address'];

        strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') ? $add_order['from_type'] = 1 : $add_order['from_type'] = 2;

        $order_id = $this->modelOrder->insertGetId($add_order);

        if(!$order_id) return false;

        $order_goods = $this->addOrderId($order_goods,$order_id);

        return ['order_goods'=>$order_goods,'order_id'=>$order_id,'cancelpay_time'=>$add_order['cancelpay_time']];
    }

}