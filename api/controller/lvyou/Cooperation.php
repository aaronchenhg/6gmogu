<?php
/**
 * Created by PhpStorm.
 * User: chenhongjin
 * Date: 2018/12/17
 * Time: 16:01
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;

class Cooperation extends ApiBase
{

    /**
     * 产品合作
     */
    public function productConnection(){
        $data = $this->logicLvyouCooperation->Connection($this->param,1);

        return $this->apiReturn($data);
    }

    /**
     * 基地合作
     */
    public function baseConnection(){

        $data = $this->logicLvyouCooperation->Connection($this->param,2);

        return $this->apiReturn($data);
    }

    /**
     * 机构合作
     */
    public function organizationConnection(){

        $data = $this->logicLvyouCooperation->Connection($this->param,3);

        return $this->apiReturn($data);
    }

    /**
     * 代理人合作
     */
    public function agentConnection(){

        $data = $this->logicLvyouCooperation->Connection($this->param,4);

        return $this->apiReturn($data);
    }


}