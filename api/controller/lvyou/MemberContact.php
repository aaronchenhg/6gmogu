<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/28 0:50
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;
use app\api\logic\Token;

class MemberContact extends ApiBase
{
    /**
     * 我的定制
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:14
     */
    public function index()
    {
        $data = $this->logicLvyouMemberContact->getContactList($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 定制线路
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:14
     */
    public function addContact()
    {

        $data = $this->logicLvyouMemberContact->addContact($this->param,Token::getCurrentUid());

        return $this->apiReturn($data);
    }

    /**
     * 修改联系人
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:14
     * @return mixed
     */
    public function updateContact()
    {

        $data = $this->logicLvyouMemberContact->updateContact($this->param);

        return $this->apiReturn($data);
    }

    /**
     * 删除联系人
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:14
     * @return mixed
     */
    public function deleteContact()
    {

        $data = $this->logicLvyouMemberContact->deleteContact($this->param);

        return $this->apiReturn($data);
    }

    /**
     * 联系人详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:14
     * @return mixed
     */
    public function getContactDetail()
    {

        $data = $this->logicLvyouMemberContact->getContactDetail($this->param);

        return $this->apiReturn($data? :[]);
    }
}