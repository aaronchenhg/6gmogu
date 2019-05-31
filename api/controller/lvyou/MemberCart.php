<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\logic\Token;
/**
 * 购物车接口控制器
 */
class MemberCart extends ApiBase
{

    /**
     * 添加购物车
     * @return mixed
     */
    public function addCart()
    {

        $has_option = input('has_option');  // 是否有规格
        $sku_id = input('sku_id');          // 规格ID
        $goods_id = input('goods_id');      // 商品ID
        $num = input('num');                // 数量

        $res =  $this->logicMemberCart->addCart(Token::getCurrentUid(),$goods_id,$sku_id,$num,$has_option);
        return $this->apiReturn($res);
    }


    /**
     * 获取会员购物车列表
     */
    public function memberCartList(){

        $res = $this->logicMemberCart->getMemberCartList(Token::getCurrentUid(),$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 修改购物车规格
     */
    public function editMemberCartSku(){
        $id = input('id');
        $sku_id = input('sku_id');
        $num = input('num');

        $res = $this->logicMemberCart->editMemberCartSku($id,$sku_id,$num);
        return $this->apiReturn($res);
    }

    /**
     * 删除会员购物车
     */
    public function delMemberCart(){
        $ids = input('ids');
        $res = $this->logicMemberCart->delMemberCart($ids);
        return $this->apiReturn($res);
    }

}
