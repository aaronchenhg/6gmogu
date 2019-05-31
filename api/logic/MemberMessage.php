<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/7 0:47
 * Copyright in Highnes
 */

namespace app\api\logic;


use app\common\model\SystemMessage;

class MemberMessage extends ApiBase
{

    /***
     * 系统消息列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 0:48
     * @return mixed
     */
    public function getSystemMessageList($uid)
    {

        // 发送系统消息
        $this->sendSystemMessage($uid);

        $this->modelMemberMessage->alias('a');

        $join               = [
            ['system_message sm', 'sm.id=a.msg_id', 'left']
        ];
        $where['a.uid']     = $uid;
        $where['sm.status'] = ['neq', -1];
        $field              = ['a.id,a.read_time,a.msg_id,sm.title,sm.content,sm.create_time,sm.image,sm.author'];
        return $this->modelMemberMessage->getList($where, $field, 'a.id desc', 8, $join);
    }


    public function getSystemMessageDetail($param)
    {

        $this->modelMemberMessage->alias('a');

        $join = [
            ['system_message sm', 'sm.id=a.msg_id', 'left']
        ];

        $where['sm.status'] = ['neq', -1];
        $where['a.id']      = @$param['id'];
        $field              = ['a.id,a.read_time,a.is_read,a.msg_id,sm.title,sm.content,sm.create_time,sm.image,sm.author'];

        $info = $this->modelMemberMessage->getInfo($where, $field, $join);
        if ($info['is_read'] == 0) {
            $this->readMessage($info['id']);

        }
        return $info;
    }

    /**
     * 阅读消息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 1:42
     * @param $id
     * @return $this
     */
    private function readMessage($id)
    {
        return \app\common\model\MemberMessage::where('id', 'eq', $id)->update(['read_time' => time(), 'is_read' => 1]);
    }


    public function sendSystemMessage($uid)
    {
        // 获取被动发送的消息
        // 判断消息是否已经发送，
        $lists = SystemMessage::where(['status' => 1, 'is_send' => 0])->field('id,create_time')->select();

        if (!empty($lists)) {
            foreach ($lists as $vo) {

                $this->addMemberMessage($uid, $vo['id']);
            }
        }

    }

    /**
     * 已存在则不添加
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $uid
     * @param $msg_id
     */
    private function isExist($uid, $msg_id)
    {
        return \app\common\model\MemberMessage::where(['uid' => $uid, 'msg_id' => $msg_id])->value('id');
    }

    /**
     * 添加消息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 1:32
     * @param $uid 用户id
     * @param $msg_id 消息id
     * @return $this
     */
    private function addMemberMessage($uid, $msg_id)
    {
        if ($this->isExist($uid, $msg_id)) {
            return false;
        }
        $data['uid']         = $uid;
        $data['msg_id']      = $msg_id;
        $data['create_time'] = time();
        $data['is_read']     = 0;

        return \app\common\model\MemberMessage::create($data);
    }
}