<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\validate;


class MemberCart extends ApiBase
{
    protected $rule = [
        'goods_id' => 'require|number',
        'sku_id' => 'number',
        'num' => 'require|number',
        'has_option' => 'require|number',
    ];
    protected $message = [
        'goods_id.require' => '缺少参数goods_id',
        'goods_id.number' => 'goods_id格式有误',
        'sku_id.number' => 'sku_id格式有误',
        'num.require' => '请选择数量',
        'has_option.require' => '缺少参数has_option',
    ];
    protected $scene = [
        'addCart' => ['goods_id','sku_id','num','has_option'],
        'editCartSku' => ['edit_sku_id'],
    ];

}