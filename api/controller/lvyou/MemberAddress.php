<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/28 0:50
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;
use app\api\logic\Token;

use app\api\controller\ApiBase;

class MemberAddress extends ApiBase
{
    /**
     * 获取会员地址列表
     */
    public function getAddressList()
    {

        $res = $this->logicMemberAddress->getAddressList(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 获取会员地址详细
     */
    public function getAddressDetail()
    {
        $res = $this->logicMemberAddress->getAddressDetail($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 添加会员地址
     * @return mixed
     */
    public function addMemberAddress()
    {

        $res = $this->logicMemberAddress->addAddress(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 编辑会员地址
     * @return mixed
     */
    public function editMemberAddress()
    {

        $res = $this->logicMemberAddress->editAddress($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 设置会员默认地址
     * @return mixed
     */
    public function setDefaultAddress()
    {

        $res = $this->logicMemberAddress->setDefaultAddress(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }


    /**
     * 删除会员地址
     * @return mixed
     */
    public function delMemberAddress()
    {

        $res = $this->logicMemberAddress->delAddress($this->param);
        return $this->apiReturn($res);
    }
}