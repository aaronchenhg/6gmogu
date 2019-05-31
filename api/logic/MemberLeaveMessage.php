<?php

namespace app\api\logic;


use app\api\error\CodeBase;
use app\api\error\LeaveMessage;
use app\api\model\MemberLeaveMessageLikeLog;
use app\api\model\MemberLeaveMessageSon;
use think\Db;
use think\Exception;

class MemberLeaveMessage extends ApiBase
{
    /**
     * 用户留言
     * @param $user_id 会员ID
     * @param $param 提交数据
     * @return array
     */
    public function addLeaveMessage($user_id, $param)
    {

        if (!$this->validateLeaveMessage->scene('add')->check($param)) {
            return LeaveMessage::leaveMessage(5060002, $this->validateLeaveMessage->getError());
        }
        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');

        $sensitiveLexicon = explode(',',$configInfo['value']);

        $isSensitive = sensitiveLexiconJudge($param['content'],$sensitiveLexicon);

        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        $images = [];
        if (!empty($param['images'])) {

            $images = explode(',', $param['images']);
        }
        $user_info = $this->modelMember->getInfo(['id' => $user_id], 'nickname,headimgurl');
        Db::startTrans();
        try {
            //数据组装
            $save['user_id']      = $user_id;
            $save['nickname']     = $user_info['nickname'];
            $save['headimgurl']   = $user_info['headimgurl'];
            $save['uniacid']      = 1;
            $save['content']      = $param['content'];
            $save['content_code'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $param['content']);
            $save['images']       = serialize($images);
            $save['create_time']  = time();
            $save['address']  = $param['address'];
            $res                  = $this->modelMemberLeaveMessage->create($save);

            Db::commit();

        } catch (Exception $e) {
            Db::rollback();
            return CodeBase::$failure;
        }
        return $res;
//        LeaveMessage::leaveMessage(0, '留言成功');
    }

    /**
     * 留言评论与回复
     * @param $comment_id 留言ID
     * @param $content 评论内容
     * @param $from_user_id 评论人ID
     * @param $to_user_id 被评论人ID
     * @param $type 评论类型（1.评论 2.回复）
     */
    public function memberLeaveMessageComment($message_id, $content, $from_user_id, $to_user_id, $type)
    {

        if (!$this->validateLeaveMessage->scene('leave_message_comment')->check(input())) {
            return LeaveMessage::leaveMessage(5060002, $this->validateLeaveMessage->getError());
        }

        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');

        $sensitiveLexicon = explode(',',$configInfo['value']);

        $isSensitive = sensitiveLexiconJudge($content,$sensitiveLexicon);

        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        $data['type']            = $type;
        $data['message_id']      = $message_id;
        $data['content']         = $content;
        $data['content_code']    = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content);
        $data['from_user_id']    = $from_user_id;
        $data['from_nickname']   = $this->getMemberField($from_user_id, 'nickname');
        $data['from_headimgurl'] = $this->getMemberField($from_user_id, 'headimgurl');
        $data['to_user_id']      = $to_user_id;
        $data['to_nickname']     = $this->getMemberField($to_user_id, 'nickname');
        $data['to_headimgurl']   = $this->getMemberField($to_user_id, 'headimgurl');
        $data['create_time']     = time();

        $res = $this->modelMemberLeaveMessageSon->insertGetId($data);

        if (!$res) {
            return CodeBase::$failure;
        }
        //如果是评论to_user_id等于自己
        if($type == 1){
            $to_user_id = $this->modelMemberLeaveMessage->where('id',$message_id)->value('user_id');
        }
        //点赞和评论记录
        insertTopicsOperationLog($message_id,$to_user_id,$from_user_id,2,$content,$res,1);
        //添加评论数量
        $res                            = $this->modelMemberLeaveMessage->where('id', $message_id)->setInc('comment_num', 1);
        $commentInfo['from_nickname']   = $data['from_nickname'];
        $commentInfo['from_headimgurl'] = $data['from_headimgurl'];
        $commentInfo['to_nickname']     = $data['to_nickname'];
        $commentInfo['to_headimgurl']   = $data['to_headimgurl'];
        $commentInfo['content']         = $data['content'];
//        return CodeBase::$success;
        return $commentInfo;

    }

    /**
     * 获取用户字段
     * @return mixed
     */
    public function getMemberField($user_id, $field)
    {
        return $this->modelMember->where('id', $user_id)->value($field);
    }

    /**
     * 留言列表
     * @param $where
     */
    public function leaveMessageList($where)
    {

        $this->modelMemberLeaveMessage->alias('ml');

        $join  = [
            ['member m', 'm.id=ml.user_id', 'left']
        ];
        $field = 'ml.id,content,images,like_num,comment_num,browse_num,ml.create_time,ml.nickname,ml.headimgurl,ml.address,m.id as user_id';
        $list  = $this->modelMemberLeaveMessage->getList($where, $field, 'create_time desc', 6, $join);

        if (empty($list)) {
            return [];
        }
        //浏览日志
        insertAccessStatist(1,2,isMobile());
        foreach ($list as $key => $val){
            //评论列表
            $list[$key]['commtent_list'] = $this->getCommentLists($val['id']);
            $list[$key]['likes_user_names'] = MemberLeaveMessageLikeLog::getClicklikeNickname($val['id']);
        }

        return $list;
    }

    /**
     * 获取评论列表
     * @param $message_id
     */
    public function getCommentLists($message_id)
    {

        $model = new MemberLeaveMessageSon();

        $where['message_id'] = $message_id;
        $where['status'] = 1;

        $result = $model->where(['message_id'=>$message_id])
            ->order('create_time asc')
            ->field('id,type,to_user_id,to_nickname,from_user_id,from_nickname,content')
            ->select();

        return $result ? $result : [];
    }


    /**
     * 留言
     * @param $where
     */
    public function memberLeaveMessageList($where)
    {

        $this->modelMemberLeaveMessage->alias('ml');

        $join  = [
            ['member m', 'm.id=ml.user_id', 'left']
        ];
        $field = 'ml.id,content,user_id,images,like_num,comment_num,browse_num,
        ml.create_time,m.nickname_code,m.headimgurl,ml.address';
        $list  = $this->modelMemberLeaveMessage->getList($where, $field, 'create_time desc', 6, $join);

        if (empty($list)) {
            $list = [];
        }
        foreach ($list as $key => $val){
            //评论列表
            $list[$key]['commtent_list'] = $this->getCommentLists($val['id']);
            $list[$key]['likes_user_names'] = MemberLeaveMessageLikeLog::getClicklikeNickname($val['id']);
        }


        return $list;
    }

    /**
     * 用户留言详细
     * @param $id 留言ID
     * @param $where 条件
     * @param $user_id 用户ID
     * @return mixed
     */
    public function memberLeaveMessageDetail($id, $where, $user_id)
    {

        if (empty($id) || $id < 0) {
            return CodeBase::$idIsNull;
        }

        $where['ml.id'] = $id;
        $this->modelMemberLeaveMessage->alias('ml');

        $field = 'ml.id,content,user_id,images,like_num,comment_num,browse_num,
        ml.create_time,ml.nickname as nickname_code,ml.headimgurl,ml.address';
        $join  = [
            ['member m', 'm.id=ml.user_id', 'left']
        ];
        $info  = $this->modelMemberLeaveMessage->getInfo($where, $field, $join);

        if (!empty($info)) {
            $info['is_like'] = MemberLeaveMessageLikeLog::leaveMessageIsLike($info['id'], 1, $user_id);

//            $info['comment_list'] = MemberLeaveMessageSon::getLeaveMessageList($id, $user_id);
            $info['comment_list'] = $this->getCommentLists($id);
            $info['likes_user_names'] = MemberLeaveMessageLikeLog::getClicklikeNickname($id);
            //增加浏览记录
//            isInsertBrowseLog($id, $info['user_id']);

        } else {
            $info = [];
        }

        return $info;
    }

    /**
     * 用户留言和评论点赞
     * @param $from_user_id 点赞人ID
     * @param $param 数据
     * @return array
     */
    public function memberLeaveMessageLike($from_user_id, $param)
    {

//        halt($param);
        if (!$this->validateLeaveMessage->scene('member_feedback_like')->check($param)) {
            return LeaveMessage::leaveMessage(5060002, $this->validateLeaveMessage->getError());
        }
        //是否点赞
        $is_like = MemberLeaveMessageLikeLog::leaveMessageIsLike($param['entity_id'], $param['type'], $from_user_id);
        if ($is_like == 1) {
            return LeaveMessage::leaveMessage(5060003, '你已经点过赞了');
        }
        Db::startTrans();
        try{

            //增加数量1是留言点赞，2是留言评论点赞
            if ($param['type'] == 1) {
                Db::name('memberLeaveMessage')->where('id', $param['entity_id'])->setInc('like_num', 1);
            } elseif ($param['type'] == 2) {
                Db::name('memberLeaveMessageSon')->where('id', $param['entity_id'])->setInc('like_num', 1);
            }

            MemberLeaveMessageLikeLog::insertLeaveMessageLikeLog($from_user_id, $param['to_user_id'], $param['type'], $param['entity_id']);

            //发表话题的用户ID
            $topicsUid = Db::name('memberLeaveMessage')->where('id',$param['entity_id'])->value('user_id');
            //点赞评论记录
            insertTopicsOperationLog($param['entity_id'],$topicsUid,$from_user_id,1);

            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            return CodeBase::$failure;
        }

        return LeaveMessage::leaveMessage(0, '点赞成功');

    }

    /**
     * 用户留言评论列表
     * @param $user_id 用户ID
     */
    public function memberLeaveMessageCommentList($user_id)
    {

        $this->modelMemberLeaveMessageSon->alias('ml');

        $where['from_user_id'] = $user_id;
        $where['type']         = 1;

        $join  = [
            ['member m', 'm.id=ml.from_user_id', 'left']
        ];
        $field = 'ml.id,content,ml.message_id,ml.create_time,m.nickname_code,m.headimgurl';
        $list  = $this->modelMemberLeaveMessageSon->getList($where, $field, '', 6, $join);

        if (empty($list)) {
            $list = [];
        }
        foreach ($list as $key => $val) {
            $list[$key]['message_info'] = $this->messageInfo($val['message_id']);
        }

        return $list;
    }

    /**
     * 留言信息
     * @param $message_id 留言ID
     */
    public function messageInfo($message_id)
    {

        $this->modelMemberLeaveMessage->alias('ml');
        $where['ml.id'] = $message_id;

        $join = [
            ['member m', 'm.id=ml.user_id', 'left']
        ];

        $fieled = 'ml.id,user_id,content,ml.create_time,images,comment_num,browse_num,like_num,m.nickname_code,m.headimgurl,ml.address';

        $info = $this->modelMemberLeaveMessage->getInfo($where, $fieled, $join);

        return $info;
    }

    /**
     * 用户留言数量和评论数量
     */
    public function memberLeaveMessageNum($user_id)
    {

        $where['ml.status']  = 1;
        $where['ml.user_id'] = $user_id;

        $data['leave_message_list_num'] = count($this->memberLeaveMessageList($where));

        $data['comment_list_num'] = count($this->memberLeaveMessageCommentList($user_id));

        return $data;

    }

    /**
     * 删除ID
     * @param $comment_id 评论ID
     */
    public function delComment($comment_id){

        if(empty($comment_id)){
            return CodeBase::$idIsNull;
        }
        $result = $this->where('id',$comment_id)->delete();

        if(!$result){
            return CodeBase::$failure;
        }

        return CodeBase::$success;
    }




}