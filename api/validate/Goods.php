<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\validate;


class Goods extends ApiBase
{
    protected $rule = [
        'goodsdata' => 'require|in:1,2,3,4,5,6,7,8',
        'cateid' => 'checkIsRequire',
        'goodssort' => 'require|in:0,1,2,3',
        'goodsnum' => 'require'
    ];
    protected $message = [
        'goodsdata.require' => '商品类型必填',
        'goodsdata.in' => '商品类型错误',
        'goodstype.checkIsRequire' => '商品类型值错误',
        'goodssort.require' => '商品排序必填',
        'goodssort.in' => '商品排序错误',
        'goodsnum.require' => '商品显示数量必填',
    ];
    protected $scene = [
        'first' => ['goodsdata','cateid','goodssort','goodsnum']
    ];
    protected function checkIsRequire($value,$rule = '',$data)
    {
        //商品分类或分组
        if(in_array($data['goodsdata'],[1,2]) && empty($value))
        {
            return '商品分类/组信息必填';
        }
        return true;
    }
}