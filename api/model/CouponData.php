<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class CouponData extends ApiBase
{
    public function getEndTimeAttr($name)
    {
        return $name > 0 ? date('Y-m-d',$name) : 0;
    }

}