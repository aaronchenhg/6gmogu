<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\common\model\TopicsOperationLog;
use app\api\logic\Token;

/**
 * 用户留言
 */
class MemberLeaveMessage extends ApiBase
{
    /**
     * 用户留言
     */
    public function addLeaveMessage()
    {

        $result = $this->logicMemberLeaveMessage->addLeaveMessage(Token::getCurrentUid(), $this->param);

        return $this->apiReturn($result);
    }

    /**
     * 商城留言列表
     */
    public function leaveMessageList()
    {
        $where['ml.status']   = 1;
        $where['ml.is_audit'] = 1;
        $keywords             = input('keywords');
        $keywords && $where['ml.content|ml.nickname'] = ['like', "%$keywords%"];
        $result = $this->logicMemberLeaveMessage->leaveMessageList($where);

        return $this->apiReturn($result);
    }

    /**
     * 用户留言列表
     */
    public function myLeaveMessageList()
    {

        $where['ml.status']  = 1;
        $where['ml.user_id'] = Token::getCurrentUid();
        $keywords             = input('keywords');
        $keywords && $where['ml.content|m.nickname_code'] = ['like', "%$$keywords%"];
        $result = $this->logicMemberLeaveMessage->memberLeaveMessageList($where);

        return $this->apiReturn($result);
    }

    /**
     * 用户留言详细
     */
    public function memberLeaveMessageDetail()
    {

        $id                 = input('id');
        $user_id            = Token::getCurrentUid();
        $where['ml.status'] = 1;
        $result             = $this->logicMemberLeaveMessage->memberLeaveMessageDetail($id, $where, $user_id);

        return $this->apiReturn($result);
    }

    /**
     * 删除评论
     */
    public function delComment(){

        //评论ID
        $id                 = input('id');
//        $user_id            = Token::getCurrentUid();
        $result             = $this->logicMemberLeaveMessage->delComment($id);

        return $this->apiReturn($result);

    }

    /**
     * 用户评论与回复
     */
    public function memberLeaveMessageComment()
    {

        $message_id = input('message_id');
        $to_user_id = input('to_user_id', 0);
        $content    = input('message_content');
        $type       = input('type', 1);

        $result = $this->logicMemberLeaveMessage->memberLeaveMessageComment($message_id, $content, Token::getCurrentUid(), $to_user_id, $type);

        return $this->apiReturn($result);
    }

    /**
     * 留言点赞和评论点赞
     */
    public function memberLeaveMessageLike()
    {
        $from_user_id = Token::getCurrentUid();

        $res = $this->logicMemberLeaveMessage->memberLeaveMessageLike($from_user_id, $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 用户留言的评论列表
     */
    public function memberLeaveMessageCommentList()
    {
        $user_id = Token::getCurrentUid();

        $res = $this->logicMemberLeaveMessage->memberLeaveMessageCommentList($user_id);
        return $this->apiReturn($res);
    }

    /**
     *
     * @return mixed
     */
    public function memberLeaveMessageNum()
    {

        $user_id = Token::getCurrentUid();
        $res     = $this->logicMemberLeaveMessage->memberLeaveMessageNum($user_id);
        return $this->apiReturn($res);
    }

    /**
     * 获取动态消息
     */
    public function getDynamicNews(){
        $user_id = Token::getCurrentUid();

        $res = TopicsOperationLog::getDynamicNews($user_id);

        return $this->apiReturn($res);
    }

    /**
     * 动态消息列表
     * @throws SuccessException
     */
    public function getDynamicNewsList(){
        $uid = Token::getCurrentUid();
        $page = input('page',1);
        $size = input('size',5);
        $is_read = input('is_read',2);

        $res = TopicsOperationLog::getDynamicNewsList($uid,$page,$size,true,$is_read);

        return $this->apiReturn($res);
    }


}
