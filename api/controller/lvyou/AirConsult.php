<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/27 20:20
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;

class AirConsult extends ApiBase
{
    /**
     * addAirConsult
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function addAirConsult()
    {
        $data = $this->logicLvyouAirConsult->addConsult($this->param,$this->user_id);
        return $this->apiReturn($data);
    }

    /**
     * getAirConsult
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function getAirConsult()
    {
        $data = $this->logicLvyouAirConsult->getAirConsult($this->param, $this->user_id);
        return $this->apiReturn($data);
    }

    /**
     * getConsultDetail
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:29
     * @return mixed
     */
    public function getConsultDetail()
    {
        $data = $this->logicLvyouAirConsult->getConsultDetail($this->param);
        return $this->apiReturn($data);
    }
}