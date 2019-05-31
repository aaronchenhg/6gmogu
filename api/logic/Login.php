<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 17:54
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\common\model\Member as MemberModel;
use app\lib\exception\ForbiddenException;
use app\supplier\model\Sms;

class Login extends ApiBase
{


    /**
     * 用户登陆
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 18:44
     * @param $param
     * @return mixed
     * @throws ForbiddenException
     */
    public function login($param)
    {
        $this->validateRegister->goCheck('login');
        $data = $this->validateRegister->getDataByRule($param);
        // :todo 判断用户是否已经存在
        $user_id = MemberModel::userExistByMobile($data['mobile']);
        if (empty($user_id)) {
            throw new ForbiddenException(['msg' => '用户不存在']);
        }

        $res = MemberModel::getUserInfoByID($user_id);

        if ($res['password'] != md5($data['password'])) {
            throw new ForbiddenException(['msg' => '密码不正确']);
        }

        $_data['openid']     = $res['openid'];
        $_data['nickname']   = $res['nickname'];
        $_data['mobile']     = $res['mobile'];
        $_data['realname']   = $res['realname'];
        $_data['headimgurl'] = $res['headimgurl'];
        $_data['sex']        = $res['sex'];
        $_data['email']      = $res['email'];
        $_data['birthday']   = $res['birthday'];

        $_data['token'] = Token::saveToCache($res);
        return $_data;

    }

    /**
     * 注册
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 18:40
     * @param $param
     * @return mixed
     */
    public function register($param)
    {
        $this->validateRegister->goCheck();
        $data = $this->validateRegister->getDataByRule($param);

//        halt($data);
        $res = Sms::where(['mobile' => $data['mobile'], 'code' => $data['code']])->where('sendtime', 'gt', time())->value('id');
        if (empty($res)) {
            throw new ForbiddenException(['msg' => '验证码不存在或已过期']);
        }

        // :todo 判断用户是否已经存在
        $user_id = MemberModel::userExistByMobile($data['mobile']);
        if (empty($user_id)) {
            $inster['mobile']        = $data['mobile'];
            $inster['password']      = md5($data['password']);
            $inster['create_time']   = time();
            $inster['headimgurl']   = config('setting.headimgurl');
            $inster['level']         = 1;
            $inster['nickname']      = '用户' . $data['mobile'];
            $inster['nickname_code'] = '用户' . $data['mobile'];
            $user_id                     = MemberModel::insert($inster,false,true);

            $res                     = MemberModel::getUserInfoByID($user_id);
        } else {
            $res = MemberModel::getUserInfoByID($user_id);
        }

        $_data['openid']     = $res['openid'];
        $_data['nickname']   = $res['nickname'];
        $_data['mobile']     = $res['mobile'];
        $_data['realname']   = $res['realname'];
        $_data['headimgurl'] = $res['headimgurl'];
        $_data['sex']        = $res['sex'];
        $_data['email']      = $res['email'];
        $_data['birthday']   = $res['birthday'];

        $_data['token'] = Token::saveToCache($res);
        return $_data;
    }

    /**
     * 重置密码
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 18:57
     * @param $param
     * @return mixed
     * @throws ForbiddenException
     */
    public function setPassword($param)
    {
        $this->validateRegister->goCheck();
        $data = $this->validateRegister->getDataByRule($param);

//        halt($data);
        $res = Sms::where(['mobile' => $data['mobile'], 'code' => $data['code']])->where('sendtime', 'gt', time())->value('id');
        if (empty($res)) {
            throw new ForbiddenException(['msg' => '验证码不存在或已过期']);
        }

        // :todo 判断用户是否已经存在
        $user_id = MemberModel::userExistByMobile($data['mobile']);
        if (empty($user_id)) {
            throw new ForbiddenException(['msg' => '用户不存在']);
        }
        $update['password']    = md5($data['password']);
        $update['update_time'] = time();
        $update['id']          = $user_id;

        $re = MemberModel::where('id',$user_id)->update($update);

        $res = MemberModel::getUserInfoByID($user_id);


        $_data['openid']     = $res['openid'];
        $_data['nickname']   = $res['nickname'];
        $_data['mobile']     = $res['mobile'];
        $_data['realname']   = $res['realname'];
        $_data['headimgurl'] = $res['headimgurl'];
        $_data['sex']        = $res['sex'];
        $_data['email']      = $res['email'];
        $_data['birthday']   = $res['birthday'];

        $_data['token'] = Token::saveToCache($res);
        return $_data;
    }

}