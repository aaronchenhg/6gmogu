<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class ReturnGoods extends ApiBase
{
    public function getAddTimeAttr($name)
    {
        return $name > 0 ? date('Y-m-d H:i:s',$name) : 0;
    }


}