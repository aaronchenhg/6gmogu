<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\model\MemberFeedback as MemberFeedbackModel;
use app\lib\exception\ForbiddenException;

class MemberFeedback extends ApiBase
{


    /**
     * 添加意见反馈
     * @param $user_id 会员ID
     * @param $param 数据
     * @return array
     */
    public function addFeedback($user_id, $param)
    {

        $this->validateMemberFeedback->goCheck('add');

        $data = $this->validateMemberFeedback->getDataByRule($param);

        //整理数据
        $data['user_id']     = $user_id;
        $data['create_time'] = time();

        MemberFeedbackModel::startTrans();
        try {
            $res = MemberFeedbackModel::create($data);
            MemberFeedbackModel::commit();
        } catch (Exception $exception) {

            MemberFeedbackModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;

    }


    /**
     * 反馈列表
     * @return mixed
     */
    public function getMemberFeedbackList($user_id)
    {

        $where['user_id'] = $user_id;
        $where['status']  = 1;

        $field = ['id,content,create_time,type_var,rely_content,rely_time'];

        return $this->modelMemberFeedback->getList($where, $field, 'id desc', DB_LIST_ROWS);

    }

    /**
     * 意见反馈详细
     * @param $user_id 会员ID
     * @param $id 反馈ID
     */
    public function getMemberFeedbackDetailed($user_id, $id)
    {

        if (empty($id)) {
            return CodeBase::$idIsNull;
        }

        $where['user_id'] = $user_id;
        $where['id']      = $id;

        $field = ['id,content,rely_content,create_time,rely_time'];

        return $this->modelMemberFeedback->getInfo($where, $field);
    }

    /**
     * 反馈类型列表
     * @return mixed
     */
    public function getMemberFeedbackType($user_id)
    {

        $where['status'] = 1;

        $field = ['id,name,checkd'];

        return $this->modelMemberFeedbackType->getList($where, $field, 'sort asc', DB_LIST_ROWS);

    }


}