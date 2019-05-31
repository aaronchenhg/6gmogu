<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/27 20:20
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;
use app\api\logic\Token;

class LineConsult extends ApiBase
{
    /**
     * 添加咨询
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function addLineConsult()
    {
        $data = $this->logicLvyouLineConsult->addConsult($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 咨询列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function getLineConsult()
    {
        $data = $this->logicLvyouLineConsult->getLineConsult($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 我的咨询
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 22:26
     * @return mixed
     */
    public function getMyLineConsult()
    {
        $data = $this->logicLvyouLineConsult->getLineConsult($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 咨询详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function getConsultDetail()
    {
        $data = $this->logicLvyouLineConsult->getConsultDetail($this->param);
        return $this->apiReturn($data);
    }
}