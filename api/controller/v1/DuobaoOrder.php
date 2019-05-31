<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9 0009
 * Time: 14:19
 */

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;

class DuobaoOrder extends ApiBase
{
    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }

    /**
     * 创建订单信息
     * User: 李姣
     * Date: 2018/08/09
     */
    public function buynow()
    {
        $result = $this->logicDuobaoOrder->placeOrder($this->param,$this->user_id);

        return  $this->apiReturn($result);
    }

    /**
     * 中奖信息接口
     * User: 李姣
     * Date: ${DATE}
     */
    public function prizeInfo()
    {
        $result = $this->logicDuobaoOrder->getPrizeInfo($this->param,$this->user_id);

        return  $this->apiReturn($result);
    }

    /**
     * 领奖
     * User: 李姣
     * @return mixed
     * Date: ${DATE}
     */
    public function takePrize()
    {
        $result = $this->logicDuobaoOrder->takePrize($this->param,$this->user_id,$this->from_type);

        return  $this->apiReturn($result);
    }
    public function failsave()
    {
        $result = $this->logicDuobaoOrder->setFailSave($this->param,$this->user_id);

        return  $this->apiReturn($result);
    }
}