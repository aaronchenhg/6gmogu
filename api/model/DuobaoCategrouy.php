<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/21 0021
 * Time: 14:35
 */

namespace app\api\model;


class DuobaoCategrouy extends ApiBase
{
    public function getDealGalleryAttr($value, $data)
    {
        return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$value;
    }
}