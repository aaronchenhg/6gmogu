<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\logic;

class GoodsOption extends ApiBase
{

    /**
     * 获取商品规格库存
     * @param $sku_id
     */
    public function getGoodsSkuStock($sku_id){

        $info = $this->modelGoodsOption->getInfo(['id'=>$sku_id],'stock');

        return $info;
    }

}