<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class MemberLeaveMessageSon extends ApiBase
{

    /**
     * 获取留言的评论列表
     * @param $message_id 留言id
     * @param $user_id 用户id
     */
    public static function getLeaveMessageList($message_id,$user_id){

        $where['message_id'] = $message_id;
        $where['status'] = 1;
        $where['is_audit'] = 1;

        $list = self::where($where)->field('id,content,from_user_id,to_user_id,from_nickname,from_headimgurl,like_num,create_time')->select();
        if(!empty($list)){
            foreach ($list as $key => $val){
                $list[$key]['comment_is_like'] = MemberLeaveMessageLikeLog::leaveMessageIsLike($val['id'],2,$user_id);
            }
        }

        return $list;

    }


}