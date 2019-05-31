<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/19 0019
 */


namespace app\api\error;


class Goods
{
    public static $goodsInfoNull = [API_CODE_NAME =>1050001, API_MSG_NAME => '商品信息丢失'];

    public static $goodsNoSpecNull = [API_CODE_NAME =>1050001, API_MSG_NAME => '商品没有多规格'];

    public static $goodsNumNull = [API_CODE_NAME =>1050001, API_MSG_NAME => '商品已售罄'];
}