<?php

namespace app\api\controller;

use app\api\logic\Token;
use app\common\controller\ControllerBase;

/**
 * 首页控制器
 */
class Index extends ControllerBase
{
    
    /**
     * 首页方法
     */
    public function index()
    {
        $token = input('token');
        $cache = Token::getCurrentCache($token);
        halt($cache);
    }
}
