<?php

namespace app\api\behavior;


use app\api\logic\LvyouOrder;
use app\api\service\AccessToken;
use app\api\service\Order;

class CORS
{
    public function appInit(&$params)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
        if(request()->isOptions()){
            exit();
        }

        $newLvyouOrder = new LvyouOrder();
        $newLvyouOrder->batchCancelOrder();

    }
}