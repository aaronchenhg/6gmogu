<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 23:26
 * Copyright in Highnes
 */

namespace app\api\model;


use app\common\model\ModelBase;

class Article extends ModelBase
{
    public function getImgAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}