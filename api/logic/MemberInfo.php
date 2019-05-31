<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/7 18:35
 * Copyright in Highnes
 */

namespace app\api\logic;


use app\lib\exception\ForbiddenException;
use app\supplier\model\Sms;

class MemberInfo extends ApiBase
{
    /**
     * 发送验证码
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $param
     * @param $uid
     * @return $this|array
     * @throws ForbiddenException
     */
    public function sendMobileCode($param, $uid)
    {

        $this->validateBindMemberInfo->goCheck('mobile');

        $data   = $this->validateBindMemberInfo->getDataByRule($param);
        $mobile = $data['mobile'];

        if ($this->isExistCode($mobile, 10)) {
            throw new ForbiddenException(['msg' => '5分钟内不能连续发送验证码']);
        }

        $code = $this->getCode();
        $msg  = "【研学旅游】验证码：{$code}，您正在进行绑定手机操作，(请勿泄漏)。";
        $res  = sendSms($mobile, $msg);

        if (array_key_exists('returnstatus', $res) && $res['returnstatus'] == 'Success') {
            return Sms::create(['sendtime' => time() + 60 * 5, 'lose' => 0, 'mobile' => $mobile, 'content' => $msg, 'code' => $code, 'type' => 10, 'userid' => $uid]);
        } else {
            return $res;
        }
    }


    private function getCode()
    {
        return $code = rand(100000, 999999);
    }

    /**
     * 判断验证码是否已经发送过
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 19:18
     * @param $mobile
     * @param int $type
     * @return mixed
     */
    private function isExistCode($mobile, $type = 10)
    {
        return Sms::where(['mobile' => $mobile, 'type' => $type])->where('sendtime', 'gt', time())->value('id');
    }


    public function sendEmailCode($param, $uid)
    {
        $this->validateBindMemberInfo->goCheck('email');

        $data  = $this->validateBindMemberInfo->getDataByRule($param);
        $email = $data['email'];

        if ($this->isExistCode($email, 20)) {
            throw new ForbiddenException(['msg' => '5分钟内不能连续发送验证码']);
        }


        $code = $this->getCode();
        $msg  = "【研学旅游】验证码：{$code}，您正在进行绑定邮箱操作，(请勿泄漏)。";
        $res  = SendMail('研学旅游邮箱验证码', $email, $msg);

        if ($res) {
            return Sms::create(['sendtime' => time() + 60 * 5, 'lose' => 0, 'mobile' => $email, 'content' => $msg, 'code' => $code, 'type' => 20, 'userid' => $uid]);
        } else {
            return [];
        }
    }


    public function bindMobile($param, $uid)
    {
        $this->validateBindMemberInfo->goCheck('bindMobile');
        $data = $this->validateBindMemberInfo->getDataByRule($param);
        $data = array_filter($data);
        $res  = Sms::where($data)->where('sendtime', 'gt', time())->value('id');
        if (empty($res)) {
            throw new ForbiddenException(['msg' => '验证码已过期']);
        }

        $data['update_time'] = time();
        $res                 = \app\common\model\Member::where('id', 'eq', $uid)->update($data);
        return [$res];
    }

    public function bindEmail($param, $uid)
    {
        $this->validateBindMemberInfo->goCheck('bindEmail');
        $data = $this->validateBindMemberInfo->getDataByRule($param);
        $data = array_filter($data);

        $res = Sms::where(['mobile'=>$data['email'],'code'=>$data['code']])->where('sendtime', 'gt', time())->value('id');
        if (empty($res)) {
            throw new ForbiddenException(['msg' => '验证码已过期']);
        }

        $data['update_time'] = time();
        $res                 = \app\common\model\Member::where('id', 'eq', $uid)->update($data);
        return [$res];
    }
}