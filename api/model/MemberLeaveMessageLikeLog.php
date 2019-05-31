<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class MemberLeaveMessageLikeLog extends ApiBase
{


    /**
     * 添加点赞记录
     * @param $from_user_id
     * @param $to_user_id
     * @param $type
     * @param $entity_id
     */
    public static function insertLeaveMessageLikeLog($from_user_id,$to_user_id,$type,$entity_id){

        $data['from_user_id'] = $from_user_id;
        $data['to_user_id'] = $to_user_id;
        $data['type'] = $type;
        $data['entity_id'] = $entity_id;
        $data['create_time'] = time();
        $data['update_time'] = time();

        return self::insert($data);

    }

    /**
     * 留言或评论是否点赞
     * @param $id 评论ID
     * @param $type 点赞类型（1.评论点赞 2.评论子表点赞）
     * @param $type 点赞用户ID
     */
    public static function leaveMessageIsLike($id,$type,$user_id)
    {

            $where['entity_id'] = $id;
            $where['type'] = $type;
            $where['from_user_id'] = $user_id;
            $res = self::where($where)->value('id');

            return empty($res) ? 0 : 1;
    }

    /**
     * 获取点赞用户昵称
     * @param $message_id 消息ID
     */
    public static function getClicklikeNickname($message_id){

        $where['entity_id'] = $message_id;
        $where['type'] = 1;
        $list = self::where($where)
            ->alias('a')
            ->join('member m','m.id=a.from_user_id','left')
            ->field('nickname_code')
            ->select();
        $nickname = '';
        foreach ($list as $k => $v){
            $nickname .= $v['nickname_code'].'<|>';
        }
//            print_r($nickname);exit;
//            $nickname = substr($nickname,0,-1);
        $nickname = rtrim($nickname, "<|>");

        $nickname =  str_replace("<|>","、",$nickname);

        return !empty($nickname) ? $nickname : null;

    }


}