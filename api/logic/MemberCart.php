<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\error\MemberCart as MemberCartError;

class MemberCart extends ApiBase
{

    /**
     * 加入购物车
     * @param $user_id
     * @param $goods_id
     * @param $sku_id
     * @param $num
     * @param $has_option
     * @return array
     */
    public function addCart($user_id,$goods_id,$sku_id,$num,$has_option)
    {
//        halt(compact($user_id,$goods_id,$sku_id,$num,$has_option));
        if(!$this->validateMemberCart->scene('addCart')->check(input()))
        {
            return MemberCartError::memberCart('300003',$this->validateMemberCart->getError());
        }

        //检测商品是否存在
        $where['id'] = $goods_id;
        $where['status'] = 1;
        $goods = $this->modelGoods->where($where)->field('id')->find();
        if(empty($goods)){
            return MemberCartError::$goodsIsNull;
        }

        //检查库存
        if($has_option == 1){
        $reslut = $this->logicGoodsOption->getGoodsSkuStock($sku_id);
        }else{
        $reslut = $this->logicGoods->getGoodsStock($goods_id);
        $reslut['stock'] = $reslut['total'];
        }
        if(($reslut['stock']-$num) < 0 && $reslut['stock'] != -1){
            return MemberCartError::$stockLack;
        }
        //条件
        $cartWhere['sku_id'] = $sku_id;
        $cartWhere['user_id'] = $user_id;
        $cartWhere['goods_id'] = $goods_id;
        $cartWhere['status'] = 1;
        $cart = $this->modelMemberCart->where($cartWhere)->value('id');
//        halt($cart);
        //如果购物车存在同样规格则加数量，否则新添加数据到购物车。
        if($cart){
            $res = $this->modelMemberCart->where($cartWhere)->setInc('num',$num);
        }else{
            $data = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'sku_id' => $sku_id,
                'num' => $num,
                'has_option' => $has_option,
                'create_time' => time(),
            ];
            $res = $this->modelMemberCart->insert($data);
        }

        if(!$res){
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }


    /**
     * 购物车列表
     * @param $data
     * @return mixed
     */
    public function getMemberCartList($user_id,$data){

//        halt(123);
        $this->modelMemberCart->alias('mc');
        $where['user_id'] = $user_id;
        $where['mc.status'] = 1;
        $join = [
            ['goods g','g.id=mc.goods_id','left'],
            ['goods_option go','go.id=mc.sku_id','left'],
        ];

        $field = ['mc.id,mc.sku_id,mc.num,mc.status,mc.has_option,mc.checked,g.id as goods_id,g.title,
        IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config('setting.site_url').'",g.thumb))as thumb,
        g.market_price,g.min_buy,g.max_buy,g.total,go.market_price as sku_market_price,go.title as sku_title,
        if(mc.has_option=1,go.market_price,g.market_price) as market_price'
        ];

        return $this->modelMemberCart->getList($where,$field,'mc.create_time desc',5,$join);

    }

    /**
     * 编辑购物车规格
     * @param $id 购物车ID
     * @param $sku_id 规格ID
     * @param $num 数量
     */
    public function editMemberCartSku($id,$sku_id,$num){

        if(empty($id)){
            return CodeBase::$idIsNull;
        }
        //购物车信息
        $cartInfo = $this->modelMemberCart->where('id',$id)->field('id,has_option,goods_id')->find();
        //检查库存
        if($cartInfo['has_option'] == 1){
            $reslut = $this->logicGoodsOption->getGoodsSkuStock($sku_id);
        }else{
            $reslut = $this->logicGoods->getGoodsStock($cartInfo['goods_id']);
            $reslut['stock'] = $reslut['total'];
        }
        if(($reslut['stock']-$num) < 0 && $reslut['stock'] != -1){
            return MemberCartError::$stockLack;
        }

        if(!$this->validateMemberCart->scene('editCartSku')->check(input()))
        {
            return MemberCartError::memberCart('300003',$this->validateMemberCart->getError());
        }

        $data['sku_id'] = $sku_id;
        $data['num'] = $num;
        $data['update_time'] = time();

        $res = $this->modelMemberCart->where('id','=',$id)->update($data);
        if(!$res){
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }


    /**
     * 删除购物车
     * @param $ids 购物车ID（多个）
     */
    public function delMemberCart($ids){

        if(empty($ids)){
            return CodeBase::$idIsNull;
        }

        $data['status'] = -1;
        $data['update_time'] = time();

        $res = $this->modelMemberCart->where('id','in',$ids)->update($data);
        if(!$res){
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }


}