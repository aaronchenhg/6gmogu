<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 11:16
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\common\controller\ControllerBase;
use think\Log;

class LvyouNotify extends ControllerBase
{


    public function notify()
    {

//        $res = \app\common\model\LvyouOrder::where(['id'=>8])->find();
////        halt($res);
//        $data = $this->logicLvyouCheckout->changeOrderPaid($res);
//        halt($data);
//        Log::info("=====notify=====" . var_export($res, true));
        $res  = kpay('weixin')->callback();
        $data = $this->logicLvyouCheckout->notifyProcess($res);
        Log::info("=====notify=====" . var_export($data, true));
        if ($data !== false) {
            echo "SUCCESS";
            exit;
        } else {
            echo "FIAL";
            exit;
        }
    }
}