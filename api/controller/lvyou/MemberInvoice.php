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

class MemberInvoice extends ApiBase
{
    /**
     * 获取会员地址列表
     */
    public function getInvoiceList()
    {

        $res = $this->logicMemberInvoice->getInvoiceList(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 获取会员地址详细
     */
    public function getInvoiceDetail()
    {
        $res = $this->logicMemberInvoice->getInvoiceDetail($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 添加会员地址
     * @return mixed
     */
    public function addMemberInvoice()
    {

        $res = $this->logicMemberInvoice->addInvoice(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }

    /**
     * 编辑会员地址
     * @return mixed
     */
    public function editMemberInvoice()
    {

        $res = $this->logicMemberInvoice->editInvoice($this->param);
        return $this->apiReturn($res);
    }

    /**
     * 设置会员默认地址
     * @return mixed
     */
    public function setDefaultInvoice()
    {

        $res = $this->logicMemberInvoice->setDefaultInvoice(Token::getCurrentUid(), $this->param);
        return $this->apiReturn($res);
    }


    /**
     * 删除会员地址
     * @return mixed
     */
    public function delMemberInvoice()
    {

        $res = $this->logicMemberInvoice->delInvoice($this->param);
        return $this->apiReturn($res);
    }
}