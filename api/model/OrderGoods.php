<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class OrderGoods extends ApiBase
{
    public function getBuyGoods($where,$field = 'og.buy_nums')
    {
        return self::alias('og')->join('order o','o.id=og.order_id')->where($where)->fetchSql(false)->Sum($field);
    }
}