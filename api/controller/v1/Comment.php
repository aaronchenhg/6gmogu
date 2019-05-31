<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\error\Comment as CommentError;
use app\api\logic\Token;

class Comment extends ApiBase
{
    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }

    /**
     * 商品评论列表
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodsComment()
    {
        return $this->apiReturn($this->logicComment->getCommentList($this->user_id,$this->param));
    }

    /**
     * 商品详情页面一条评论
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodsCommentOne()
    {
        return $this->apiReturn($this->logicComment->getCommentOne($this->user_id,$this->param));
    }

    /**
     *评论的下级评论
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function commentSon()
    {
        return $this->apiReturn($this->logicComment->getCommentSonList($this->user_id,$this->param));
    }
    /**
     * 评论详情
     * @comment_id 评论id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function commentDetail()
    {
        return $this->apiReturn($this->logicComment->getCommentDetail($this->user_id,$this->param));
    }
    /**
     * 添加商品评论
     */
    public function addGoodsComment0709(){
        $order_id = input('order_id');
        $goods_ids = input('goods_ids');
        $res = $this->logicComment->addGoodsComment0709($order_id,$this->user_id,$goods_ids,$this->param);
        return $this->apiReturn($res);
    }
    /**
     * 添加商品评论
     */
    public function addGoodsComment(){

        $res = $this->logicComment->addGoodsComment($this->user_id,$this->param);
        return $this->apiReturn($res);
    }

    /**
     * 评论回复
     */
    public function commentReply(){

//        if(!$this->validateOrderCommentSn->scene('comment_son')->check(input()))
//        {
//            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
//        }
//        $arr = [8,0,0,8,2,0,8,8,2,0];
//        print_r(array_sum(array_keys(array_flip($arr))));exit;


        $comment_id = input('comment_id');
        $to_user_id = input('to_user_id',0);
        $content = input('content');
        $type = input('type');


        $res = $this->logicComment->commentReply($comment_id,$content,$this->user_id,$to_user_id,$type);
        return $this->apiReturn($res);

    }

    /**
     * 评论点赞
     */
    public function commentLike(){

        $from_user_id = $this->user_id;

        $res = $this->logicComment->commentLike($from_user_id,$this->param);
        return $this->apiReturn($res);


    }
}