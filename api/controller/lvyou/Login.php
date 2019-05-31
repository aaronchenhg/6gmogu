<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 17:22
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\common\controller\ControllerBase;

class Login extends ControllerBase
{

    public function login()
    {
        $data = $this->logicLogin->login($this->param);

        $_data['code'] = 0;
        $_data['msg']  = '登陆成功';
        $_data['data'] = $data;
        return json($_data);
    }

    /**
     * register
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 18:58
     * @return \think\response\Json
     */
    public function register()
    {

        $data = $this->logicLogin->register($this->param);

        $_data['code'] = 0;
        $_data['msg']  = '注册成功';
        $_data['data'] = $data;
        return json($_data);
    }
    public function setPassword()
    {

        $data = $this->logicLogin->setPassword($this->param);

        $_data['code'] = 0;
        $_data['msg']  = '重置密码成功';
        $_data['data'] = $data;
        return json($_data);
    }
}