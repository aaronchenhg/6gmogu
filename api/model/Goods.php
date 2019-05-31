<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * <<<<<<< HEAD
 * Date: 2018/6/14 0014
 */


namespace app\api\model;

use think\Config;


class Goods extends ApiBase
{
    /**
     * @param $value 值
     * @return string
     */
//    public function getThumbAttr($value)
//    {
//          return  $value ? config('setting.site_url').$value : '';
//    }

    public function getThumbUrlAttr($value)
    {
        $arr = explode(',',$value);

        foreach ($arr as $key => $val){
            $arr[$key] = $this->prefixImgUrl($val,'');
        }

        return $arr;
    }

    /**
     * 商品详情图片增加域名地址
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param $value
     * @param $data
     * @return string
     */
    public function getContentAttr($value, $data)
    {
        $url = 'src="' . \config('setting.site_url') . '/ueditor';
        return str_replace('src="/ueditor', $url, $value);
    }

    public function getBuyContentAttr($value, $data)
    {
        $url = 'src="' . \config('setting.site_url') . '/ueditor';
        return str_replace('src="/ueditor', $url, $value);
    }

    /**
     * 获取商品基本信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getGoodsInfo($id)
    {
//        IF(thumb = "","",IF(LOCATE("http", thumb) > 0,thumb,CONCAT("'.Config::get('http_name').'/'.'",thumb)))
        $fields = "id as goods_id,cates,type,status,title as name,IF(thumb = '','',IF(LOCATE('http',thumb) > 0,thumb,CONCAT('" . Config::get('http_name') . "/',thumb))) as goods_image,
        market_price as price,product_price,cost_price,total as stock,max_buy,weight,'0' as sku_id,'' as sku_name,is_send_free,user_max_buy,total_cnf,dispatch_type,dispatch_id,is_dispatch_price,dispatch_price,is_send_free";
        return self::where('id', $id)->field($fields)->find();
    }

    /**
     * 获取商品待规格信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @param $goods_id
     * @param $sku_id
     */
    public function getGoodsSkuInfo($goods_id, $sku_id)
    {
        $where['g.id'] = $goods_id;
        $where['go.id'] = $sku_id;
        $fields = "goods_id,type,g.cates,g.status,g.title as name,IF(g.thumb = '','',IF(LOCATE('http',g.thumb) > 0,g.thumb,CONCAT('" . Config::get('http_name') . "/',g.thumb))) as goods_image,
        go.market_price as price,go.product_price,go.cost_price,go.stock,max_buy,go.id as sku_id,go.title as sku_name,specs,go.weight,g.is_send_free,g.user_max_buy,g.total_cnf
        ,g.dispatch_type,g.dispatch_id,g.is_dispatch_price,g.dispatch_price,g.is_send_free";
        return self::alias("g")->where($where)->join("GoodsOption go", "g.id=go.goods_id")->field($fields)->find();
    }
}