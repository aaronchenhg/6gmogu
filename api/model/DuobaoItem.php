<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8 0008
 * Time: 19:17
 */

namespace app\api\model;


use think\Config;

class DuobaoItem extends ApiBase
{
    public function getOriginPriceAttr($value, $data)
    {
        return sprintf("%.2f",$value);
    }
    public function getGoodsContentAttr($value, $data)
    {
        $url = 'src="' . \config('setting.site_url') . '/ueditor';
        return str_replace('src="/ueditor', $url, $value);
    }
    public function getDealGalleryAttr($value)
    {
        return Config::get('http_name').$value;
    }



}