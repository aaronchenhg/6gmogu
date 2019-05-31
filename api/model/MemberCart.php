<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class MemberCart extends ApiBase
{

    public function getCartList($ids)
    {
        $field = "user_id,goods_id,sku_id,num";
        if (is_array($ids)) {
            $cartids = $ids;
        } else {
            $cartids = explode(',', $ids);
        }
        return self::where(['id' => ['in', $cartids], 'status' => 1])->field($field)->select();
    }
}