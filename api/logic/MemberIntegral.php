<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/6 22:59
 * Copyright in Highnes
 */

namespace app\api\logic;


class MemberIntegral extends ApiBase
{


    public function getMemberIntegralList($uid)
    {
        $where['uid'] = $uid;
        $where['status']    = 1;
        $field        = 'id,all,now,number,type,remark,create_time';

        return $this->modelMemberIntegral->getList($where, $field, 'create_time desc', false);
    }

    /**
     * 增加积分记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function addIntegral($param = [])
    {
        // :TODO 添加积分
    }
}