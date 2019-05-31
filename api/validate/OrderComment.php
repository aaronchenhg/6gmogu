<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\validate;


class OrderComment extends ApiBase
{
    protected $rule = [
        'order_id' => 'require',
        'goods_id' => 'require',
        'goods_ids' => 'require',
        'user_id' => 'require',
        'content' => 'require',
        'level' => 'require',
        'comment_id' => 'require',
        'images' => 'max:1000000',
        'id' => 'require',
        //点赞comment_like
        'to_user_id' => 'require',
        'type' => 'require',
        'entity_id' => 'require',

    ];
    protected $message = [
        'order_id.require' => '订单信息必填',
        'goods_id.require' => '商品信息必填',
        'goods_ids.require' => '缺少参数商品ID',
        'user_id.require' => '用户信息必填',
        'level.require' => '选择评价星级',
        'content.require' => '给出您的宝贵意见说点什么吧',
        'comment_id.require' => '缺少参数comment_id',
        'images.max' => '评论图片',
        'id.require' => '缺少参数ID',
        //点赞comment_like
        'to_user_id.require' => '缺少参数to_user_id',
        'type.require' => '缺少参数type',
        'entity_id.require' => '缺少参数entity_id',

    ];
    protected $scene = [
        'goods' => ['goods_id'],
        'order' => ['order_id'],
        'user' => ['user_id'],
        'comment_son' => ['comment_id'],
        'goods_comment' => ['id','order_id','goods_ids','level','content','images'],
        'comment_like' => ['to_user_id','type','entity_id'],
    ];
}