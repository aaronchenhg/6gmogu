<?php

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;

/**
 * 意见反馈接口控制器
 */
class MemberFeedback extends ApiBase
{

    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }
    /**
     * 添加意见反馈
     * @return mixed
     */
    public function addFeedback()
    {
        $res =  $this->logicMemberFeedback->addFeedback($this->user_id,$this->param);
        return $this->apiReturn($res);
    }


    /**
     * 获取意见反馈列表
     */
    public function memberFeedbackList(){

        $res = $this->logicMemberFeedback->getMemberFeedbackList($this->user_id);
        return $this->apiReturn($res);
    }

    /**
     * 意见反馈详细
     */
    public function memberFeedbackDetailed(){

        $id = input('id');

        $res = $this->logicMemberFeedback->getMemberFeedbackDetailed($this->user_id,$id);
        return $this->apiReturn($res);
    }

    /**
     * 获取意见反馈类型
     */
    public function memberFeedbackType(){

        $res = $this->logicMemberFeedback->getMemberFeedbackType($this->user_id);
        return $this->apiReturn($res);
    }

}
