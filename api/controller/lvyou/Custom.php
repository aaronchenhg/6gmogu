<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 19:13
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;

class Custom extends ApiBase
{

    /**
     * 我的定制
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function index()
    {
        $data = $this->logicLvyouLineCustom->getCustomList($this->param, $this->user_id);
        return $this->apiReturn($data);
    }

    /**
     * 定制线路
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function addCustom()
    {

        $data = $this->logicLvyouLineCustom->addCustom($this->param);

        return $this->apiReturn($data);
    }

    public function getCustomDetail()
    {

        $data = $this->logicLvyouLineCustom->getCustomDetail($this->param);

        return $this->apiReturn($data);
    }
}