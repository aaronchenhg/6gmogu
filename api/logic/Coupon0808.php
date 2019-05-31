<?php

namespace app\api\model;

use think\Log;
use think\Model;

class CouponCode extends BaseModel
{

    public static function getUserCoupons($userId, $code = null)
    {
        $map = [
            'cc.uid' => $userId,
            'cc.is_used' => 2,
            'c.begin_time' => ['exp', '< now()'],
            'c.end_time' => ['exp', '> now()']
        ];
        if ($code) {
            $map['cc.code'] = $code;
        }
        $info = self::alias('cc')->where($map)
            ->join('coupons c', 'c.id = cc.coupon_id')
            ->field('c.name,c.type_id,c.face_value,c.description,cc.code,cc.is_used,cc.created_time,cc.id as codeId')
            ->order('created_time desc')
            ->select();

        return $info;
    }

    /**
     * 通过Code 获取优惠券
     * @author: chenhg <945076855@qq.com>
     * @param $code
     * @param string $userId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getCouponByCode($code, $userId = 'null')
    {
        $now = date('Y-m-d');
        $map = [
            'cc.is_used' => 2,
            'c.begin_time' => ['elt', $now], //['exp', '<', $now],
            'c.end_time' => ['egt', $now]
        ];
        if (empty($code)) {
            return null;
        }
        $userId && $map['cc.uid'] = $userId;
        $map['cc.code'] = $code;
        $info = self::alias('cc')->where($map)
            ->join('coupons c', 'c.id = cc.coupon_id')
            ->field('c.name,c.type_id,c.face_value,c.description,cc.code,cc.is_used,cc.id as codeId,cc.uid')->find();
        return $info;
    }

    /**
     * 改变优惠券状态
     * @author: chenhg <945076855@qq.com>
     * @param $codeInfo 优惠券信息
     * @param int $order_id 订单ID
     * @return false|int
     */
    public static function changeCouponStatus($codeInfo, $order_id = 0)
    {
        $data['is_used'] = 1;
        $data['order_id'] = $order_id;
        insertOrderLog($order_id, '', $codeInfo['uid'], '使用优惠券抵扣:' . $codeInfo['code']);
        return self::where('id', 'eq', $codeInfo['codeId'])->update($data);
    }

    /**
     * 自动发放优惠券
     * @author: chenhg <945076855@qq.com>
     * @param $user_id
     * @param $vip_id
     * @return bool|int|string
     */
    public function autoSendCouponCode($user_id, $vip_id)
    {
        if ($vip_id <= 0) {
            return false;
        }
        $where['posted_type'] = 2;
        $where['level_id'] = $vip_id;
        $where['status'] = 1;
        $info = Coupons::where($where)->field("id,posted_num")->find();
        return $info ? $this->insertCouponCode($info, $user_id) : false;
    }

    /**
     * 自动发放优惠券
     * @author: chenhg <945076855@qq.com>
     * @param $user_id  用户ID
     * @param $couponId 优惠券ID
     * @return bool|int|string
     */
    public function autoSendCouponCodeOfCouponId($user_id, $couponId)
    {
        if ($couponId <= 0) {
            return false;
        }
        $where['id'] = $couponId;
        $where['status'] = 1;
        $info = Coupons::where($where)->field("id,posted_num")->find();
        return $info ? $this->insertCouponCode($info, $user_id) : false;
    }


    public function autoRegisterSendCouponCode($user_id)
    {
        $where['status'] = 1;
        $where['type_id'] = 2;
//        $info = Coupons::where($where)->field("id,posted_num")->find();
        $list = Coupons::where($where)->field("id,posted_num")->select();

        if (empty($list)) {
            return false;
        }
        foreach ($list as $k => $v) {
            $this->insertCouponCode($v, $user_id);
        }

//        return $info ? $this->insertCouponCode($info, $user_id) : false;
    }


    /**
     * insertCouponCode
     * @author: chenhg <945076855@qq.com>
     * @param $data
     * @param $user_id
     * @return int|string
     */
    private function insertCouponCode($data, $user_id)
    {
        $_data = [];
        $number = intval($data['posted_num']) > 0 ? $data['posted_num'] : 1;
        for ($i = 0; $i < $number; $i++) {
            $_data[$i] = ['uid' => $user_id,
                'coupon_id' => $data['id'],
                'code' => getRandChar(10) . $user_id,
                'is_used' => 2,
                'created_time' => date("Y-m-d H:i:s", time())
            ];
        }
        Coupons::where('id', $data['id'])->setInc('createnum', $number);
        return self::insertAll($_data);
    }

    /**
     * 获取用户的可以使用的优惠券
     * @author: chenhg <945076855@qq.com>
     * @param      $userId
     * @param null $orderInfo
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getUserCheckCoupons($userId, $orderInfo = null)
    {
        $cate_ids = '';
        if (!empty($orderInfo)) {
            foreach ($orderInfo['orderGoods'] as $k => $val) {
                $cate_ids .= $val['cate_id'];
            }
            $cate_ids = array_unique(array_filter(explode(',', $cate_ids)));
        }
        $_str = ' 1 ';
        $cate_amount = [];
        $cate_amount1 = [];
        $_cate_ids = [];

        $_iscake = false;
        $cakearr = [15, 16, 17];
        foreach ($cate_ids as $key => $item) {
            $_cate_ids[] = $item;
            $_str .= " OR `c`.`cate_id` LIKE '%," . $item . ",%'";
            // 计算每个分类的商品金额
            foreach ($orderInfo['orderGoods'] as $k => $val) {
                $_tmp_cate = explode(',', $val['cate_id']);

                if (in_array($item, $_tmp_cate)) {
                    $amount = $val['real_price']; //* $val['buy_nums']
                    if (isset($cate_amount['cate_' . $item . "_amount"])) {
//                        $amount += $cate_amount['cate_' . $item . "_amount"];
                        $cate_amount['cate_' . $item . "_amount"] += $amount;
                        $cate_amount1[$k]['cate_' . $item . "_amount"] = $amount;
                        $cate_amount1[$k]['cate_id'] = $item;
                    } else {
                        $cate_amount['cate_' . $item . "_amount"] = $amount;
                        $cate_amount1[$k]['cate_' . $item . "_amount"] = $amount;
                        $cate_amount1[$k]['cate_id'] = $item;
                    }

                }
            }

            $cate_has_amount = [];

            $i = 0;
            foreach ($cate_amount as $key => $val){
                $cate_has_amount[$i]['cate_id'] = str_replace('cate_','',str_replace('_amount','',$key));
                $cate_has_amount[$i][$key] = $val;
                $i++;
            }


            $cate_has_amount = array_values($cate_has_amount);
            if (!$_iscake && in_array($item, $cakearr)) {
                $_iscake = true;
            }
        }

        $_str = str_replace(" 1  OR ", "", $_str);
        $now = date("Y-m-d ");
        if ($_iscake) { // 如果存在蛋糕商品，则分开算，不存在蛋糕商品则算总额
            $i = 0;
            $coupon_list = [];
            foreach ($cate_amount as $k => $cate_amout) {
                $sql_cate = "`c`.`cate_id` LIKE '%,$_cate_ids[$i],%'";
                $sql = "SELECT `c`.`name`,`c`.`type_id`,`c`.`face_value`,`c`.`description`,`cc`.`code`,`cc`.`is_used`,`cc`.`created_time`,cc.id as codeId ,c.cate_id,c.conditions
                FROM `koala_coupon_code` `cc` INNER JOIN `koala_coupons` `c` ON `c`.`id`=`cc`.`coupon_id` WHERE  `cc`.`uid` = $userId  AND `cc`.`is_used` = 2  
                AND ( `c`.`begin_time` <= '" . $now . "' ) AND ( `c`.`end_time` >= '" . $now . "' )  AND c.conditions <= " . $cate_amout . " AND ( $sql_cate ) 
                 ORDER BY created_time desc";
                $lists = self::query($sql);
                $coupon_list = array_merge($lists, $coupon_list);
                $i++;
            }
            $lists = [];
            foreach ($coupon_list as $k => $item) {
                $lists[$k] = $item;
            }

        } else {
            $sql = "SELECT `c`.`name`,`c`.`type_id`,`c`.`face_value`,`c`.`description`,`cc`.`code`,`cc`.`is_used`,`cc`.`created_time`,cc.id as codeId ,c.cate_id,c.conditions
                FROM `koala_coupon_code` `cc` INNER JOIN `koala_coupons` `c` ON `c`.`id`=`cc`.`coupon_id` WHERE  `cc`.`uid` = $userId  AND `cc`.`is_used` = 2
                AND ( `c`.`begin_time` <= '" . $now . "' ) AND ( `c`.`end_time` >= '" . $now . "' )  AND c.conditions <= " . $orderInfo['order']['sku_amount'] . " AND ( $_str )
                ORDER BY created_time desc";

            $lists = self::query($sql);

        }


        return ['youhui' => $lists,'money' => $cate_has_amount];
    }


}
