<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class Coupon extends ApiBase
{

    public function getTimeStartAttr($name)
    {
        return $name ? date('Y-m-d',$name) : '--';
    }

    public function getTimeEndAttr($name)
    {
        return $name ? date('Y-m-d',$name) : '--';
    }

    public function goodsCoupon()
    {
        return $this->hasMany('GoodsCoupon','coupon_id','id');
    }

    /**
     * 使用范围描述：0全店通用1指定商品可用2指定分类商品可用
     * @param $value
     * @param $data
     * @return int
     */
    public function getUseTypeTitleAttr($value, $data)
    {
        if ($data['use_type'] == 1) {
            return '指定商品';
        } elseif($data['use_type'] == 2) {
            return '指定分类商品';
        }else{
            return '全店通用';
        }
    }

}