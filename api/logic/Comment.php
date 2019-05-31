<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\error\Comment as CommentError;
use think\Db;
use think\Exception;
use think\Log;

class Comment extends ApiBase
{
    /**
     * 评论列表
     * @userid 用户id
     * @goods_id 商品id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getCommentList($userid = 1,$data = [])
    {
        if(!$this->validateOrderComment->scene('goods')->check($data))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }
        $where['oc.goods_id'] = $data['goods_id'];
//        $where['oc.user_id'] = $userid;
        $where['oc.status'] = 1;

        $field = 'oc.id,m.nickname,m.headimgurl,oc.level,oc.user_id,oc.content,oc.images,oc.browse_num,oc.like_num,oc.create_time,og.sku_name,oc.sku_id,oc.goods_id';

        $goods_info = $this->modelGoods->getInfo(['id'=>$data['goods_id']]);
        $this->modelOrderComment->alias('oc');

        if($goods_info['has_option'] == 1)
        {
            $join = [
                ['order_goods og','oc.sku_id = og.sku_id','LEFT'],
                ['member m','oc.user_id = m.id']
            ];
        }else
        {
            $join = [
                ['order_goods og','oc.goods_id = og.goods_id','LEFT'],
                ['member m','oc.user_id = m.id']
            ];
        }
        $list = $this->modelOrderComment->getList($where,$field,'oc.is_top,oc.create_time',DB_LIST_ROWS,$join,'oc.id');

        if(!empty($list))
        {
            $lists = $list->toArray();

            //完善评论图片的路径
            $lists['data'] = serializeToArray($lists['data'],'images','serialize','img_lists');

            $lists['data'] = $this->commentIsLike($lists['data'],1);

        }
        return $lists;
    }

    public function getCommentOne($userid = 1,$data = [])
    {
        $list = $this->getCommentList($userid,$data);

        $info = [];
        if(!empty($list['data']))
        {
            $info = $list['data'][0];
            $info['total'] = $list['total'];
        }
        return $info;
    }
    /**
     * 评论的下级评论
     * @userid 用户id
     * @comment_id 评论id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getCommentSonList($userid = 1,$data = [])
    {
        if(!$this->validateOrderComment->scene('comment_son')->check($data))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }
        $where['comment_id'] = $data['comment_id'];
        $where['status'] = 1;

        $list = $this->modelOrderCommentSon->getList($where,'*,IF(to_headimgurl = "",to_headimgurl,
        CONCAT("'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.'",to_headimgurl)) as to_headimgurl',
            'is_top desc,create_time desc',DB_LIST_ROWS);

        //判断是否点赞
        if(!empty($list)){
            foreach ($list as $k => $v){
                $list[$k]['is_like'] = $this->commentIsLike($v['id'],2);
            }
        }

        return $list;
    }

    /**
     * 评论详情
     * @comment_id 评论id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getCommentDetail($userid = 1,$data = [])
    {
        if(!$this->validateOrderComment->scene('comment_son')->check($data))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }
        $field = 'oc.id,oc.nickname,oc.headimgurl,oc.level,oc.content,oc.images,oc.browse_num,oc.like_num,oc.create_time,og.sku_name,oc.sku_id,oc.goods_id,oc.user_id';

        $where['oc.id'] = $data['comment_id'];

        $join = [
            ['goods g','g.id=oc.goods_id']
        ];
        $this->modelOrderComment->alias('oc');
        $has_option = $this->modelOrderComment->getInfo(['oc.id'=>$data['comment_id']],'g.has_option',$join);

        if($has_option['has_option'] == 1)
        {
            $join_comment = [
                ['order_goods og','oc.sku_id = og.sku_id','LEFT']
            ];
        }else
        {
            $join_comment = [
                ['order_goods og','oc.goods_id = og.goods_id','LEFT']
            ];
        }

        $this->modelOrderComment->alias('oc');
        $info = $this->modelOrderComment->getInfo($where,$field,$join_comment);

        if(!empty($info))
        {
            $info['is_like'] = $this->commentIsLike($info['id'],1);

            if(!empty($info['images'])){
                $imags = unserialize($info['images']);

                foreach ($imags as $k=>$img)
                {
                    $img_array[] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.$img;
                }
                $info['img_lists'] = $img_array;
            }
        }

        return $info;
    }
    /**
     * 添加商品评论
     * @param $id 订单ID
     * @param $godos_ids 商品ID（多个）
     * @param $param 数据
     */
    public function addGoodsComment0709($order_id,$user_id,$godos_ids,$data){

        if(!$this->validateOrderComment->scene('goods_comment')->check($data))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }

//        $images = json_decode($data['images'],true);

        $images = explode(',',$data['images']);
        $evaluate_status = $this->modelOrder->where('id', $order_id)->value('evaluate_status');
        //转成数组
        $goodsidarr = explode(',', $godos_ids);
        $contentArr = explode('|', $data['content']);
        $scoretArr = explode('|', $data['level']);
//        $anonymousArr = explode('|', $data['is_anonymous']);
        Db::startTrans();
          try{
              //如果评论状态等于0才能评论
              if ($evaluate_status == 0) {
                  $idslen = count($goodsidarr);
                  $save['user_id'] = $user_id;
                  $save['order_id'] = $order_id;
                  $save['uniacid'] = 1;
                  $save['images'] = serialize($images);
//                  $save['create_time'] = date('Y-m-d H:i:s');
                  $save['create_time'] = time();
                  for ($i = 0; $i < $idslen; $i++) {
//                      $save['goods_id'] = $goodsidarr[$i];
                      $save['goods_id'] = 202;
                      $save['content'] = $contentArr[$i];
                      $save['level'] = $scoretArr[$i];
//                      $save['is_anonymous'] = $anonymousArr[$i];
//                      $save['images'] = json_encode($images[$i]);
                      $save['status'] = 1;
                      $this->modelOrderComment->insert($save);

                  }
                  //改变评论状态
                  $where['id'] = $order_id;
                  $info['evaluate_status'] = 1;
                  $this->modelOrder->where($where)->update($info);
                  Db::commit();
              }else{
                  return CommentError::$alreadyCmment;
              }
          }catch (Exception $e){
              halt($e->getMessage());
              Db::rollback();
              return CodeBase::$failure;
          }
          return CodeBase::$success;

    }

    /**
     * 添加评论
     * @param $user_id 会员ID
     * @param $param 提交数据
     * @return array
     */
    public function addGoodsComment($user_id,$param){

        if(!$this->validateOrderComment->scene('goods_comment')->check($param))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }
        $images = [];
        if(!empty($param['images'])){

        $images = explode(',',$param['images']);
        }
        Db::startTrans();
        try{
            //数据组装
            $save['user_id'] = $user_id;
            $save['order_id'] = $param['order_id'];
            $save['uniacid'] = 1;
            $save['goods_id'] = $param['goods_ids'];
            $save['content'] = $param['content'];
            $save['level'] = $param['level'];
            $save['images'] = serialize($images);
            $save['create_time'] = time();

            $this->modelOrderComment->insert($save);

            //改变评论状态
            $where['id'] = $param['id'];
            $info['evaluate_status'] = 1;
            $info['update_time'] = 1;
            $this->modelOrderGoods->where($where)->update($info);
            Db::commit();

        }catch (Exception $e){
            halt($e->getMessage());
            Db::rollback();
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 评论是否点赞
     * @param $id 评论ID
     * @param $type 点赞类型（1.评论点赞 2.评论子表点赞）
     */
    public function commentIsLike($id,$type)
    {
        if(is_array($id))
        {
            $data = [];
            foreach ($id as $value)
            {
                empty($this->modelCommentLikeLog->where('entity_id','=',$value['id'])->where('type','=',$type)->value('id')) ? $value['is_like'] = 0 : $value['is_like'] = 1;
                $data[] = $value;
            }
            return $data;
        }else
        {
            $res = $this->modelCommentLikeLog->where('entity_id','=',$id)->where('type','=',$type)->value('id');

            return empty($res) ? 0 : 1;
        }
    }

    /**
     * 评论回复
     * @param $comment_id 评论ID
     * @param $content 评论ID
     * @param $from_user_id 评论人ID
     * @param $to_user_id 被评论人ID
     * @param $type 评论类型（1.评论 2.回复）
     */
    public function commentReply($comment_id,$content,$from_user_id,$to_user_id,$type){

        $data['type'] = $type;
        $data['comment_id'] = $comment_id;
        $data['content'] = $content;
        $data['from_user_id'] = $from_user_id;
        $data['from_nickname'] = $this->getMemberField($from_user_id,'nickname');
        $data['from_headimgurl'] = $this->getMemberField($from_user_id,'headimgurl');
        $data['to_user_id'] = $to_user_id;
        $data['to_nickname'] = $this->getMemberField($to_user_id,'nickname');
        $data['to_headimgurl'] = $this->getMemberField($to_user_id,'headimgurl');
        $data['create_time'] = time();

        $res = $this->modelOrderCommentSon->insert($data);

        if(!$res){
            return CodeBase::$failure;
        }
        $commentInfo['from_nickname'] = $data['from_nickname'];
        $commentInfo['from_headimgurl'] = $data['from_headimgurl'];
        $commentInfo['to_nickname'] = $data['to_nickname'];
        $commentInfo['to_headimgurl'] = $data['to_headimgurl'];
        $commentInfo['content'] = $data['content'];
//        return CodeBase::$success;
        return $commentInfo;
    }

    /**
     * 获取用户字段
     * @return mixed
     */
    public function getMemberField($user_id,$field){
        return  $this->modelMember->where('id',$user_id)->value($field);
    }

    /**
     * 评论点赞
     * @param $from_user_id 点赞会员ID
     * @param $param 数据
     */
    public function commentLike($from_user_id,$param){

        if(!$this->validateOrderComment->scene('comment_like')->check($param))
        {
            return CommentError::commentError('1060002',$this->validateOrderComment->getError());
        }

        $result = $this->modelCommentLikeLog->insertCommetnLikeLog($from_user_id,$param['to_user_id'],$param['type'],$param['entity_id']);

        if(!$result){
            return CodeBase::$failure;
        }
        return CodeBase::$success;

    }

}