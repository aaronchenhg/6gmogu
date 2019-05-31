<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/7/23 0023
 */


namespace app\api\controller\v1;

use app\api\controller\ApiBase;

class Diypage extends ApiBase
{
    public function wxfirstapp()
    {
        return $this->apiReturn($this->logicWxapp->getWxappPageInfo($this->param,'Wechat'));
    }

    /**
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function wxmemberapp()
    {
        return $this->apiReturn($this->logicWxapp->getWxappPageInfo(['type'=>3],'Wechat'));
    }

    public function startadv()
    {
        return $this->apiReturn($this->logicWxapp->getWxappPageInfo(['type'=>30],'Wechat'));
    }

}