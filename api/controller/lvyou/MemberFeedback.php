<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\logic\Token;
/**
 * 意见反馈接口控制器
 */
class MemberFeedback extends ApiBase
{

    /**
     * 添加意见反馈
     * @return mixed
     */
    public function addFeedback()
    {
        $res =  $this->logicMemberFeedback->addFeedback(Token::getCurrentUid(),$this->param);
        return $this->apiReturn($res);
    }


    /**
     * 获取意见反馈列表
     */
    public function memberFeedbackList(){

        $res = $this->logicMemberFeedback->getMemberFeedbackList(Token::getCurrentUid());
        return $this->apiReturn($res);
    }

    /**
     * 意见反馈详细
     */
    public function memberFeedbackDetailed(){

        $id = input('id');

        $res = $this->logicMemberFeedback->getMemberFeedbackDetailed(Token::getCurrentUid(),$id);
        return $this->apiReturn($res);
    }

    /**
     * 获取意见反馈类型
     */
    public function memberFeedbackType(){

        $res = $this->logicMemberFeedback->getMemberFeedbackType(Token::getCurrentUid());
        return $this->apiReturn($res);
    }

}
