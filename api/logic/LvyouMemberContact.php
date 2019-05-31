<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 17:51
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use app\common\model\LvyouMemberContact as MemberContactModel;
use app\lib\exception\ForbiddenException;
use think\Exception;


/**
 * 我的常用联系人
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\logic
 */
class LvyouMemberContact extends ApiBase
{


    /**
     * 添加联系人
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 1:07
     * @param $param
     * @param int $uid
     * @return $this
     * @throws ForbiddenException
     */
    public function addContact($param, $uid = 0)
    {
        // 验证参数是否正确
        $this->validateLvyouMemberContact->goCheck('add');
        // 添加参数到数据
        $data = $this->validateLvyouMemberContact->getDataByRule($param);

        unset($data['id']);
        $data['status']      = 1;
        $data['create_time'] = time();
        $data['uid']         = $uid;

        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');
        $sensitiveLexicon = explode(',',$configInfo['value']);
        $isSensitive = sensitiveLexiconJudge($param['name'],$sensitiveLexicon);
        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }


        MemberContactModel::startTrans();
        try {
            $res = MemberContactModel::create($data);
            MemberContactModel::commit();
        } catch (Exception $exception) {

            MemberContactModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 修改联系人
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $param
     * @return $this
     * @throws ForbiddenException
     */
    public function updateContact($param)
    {
        // 验证参数是否正确
        $this->validateLvyouMemberContact->goCheck('edit');
        // 添加参数到数据
        $data = $this->validateLvyouMemberContact->getDataByRule($param);

        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');
        $sensitiveLexicon = explode(',',$configInfo['value']);
        $isSensitive = sensitiveLexiconJudge($param['name'],$sensitiveLexicon);
        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        $data['update_time'] = time();

        MemberContactModel::startTrans();
        try {
            $res = MemberContactModel::update($data);
            MemberContactModel::commit();
        } catch (Exception $exception) {

            MemberContactModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 删除联系人
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $param
     * @return $this
     * @throws ForbiddenException
     */
    public function deleteContact($param)
    {
        // 验证参数是否正确
        $this->validateLvyouMemberContact->goCheck('del');
        // 添加参数到数据
        $data = $this->validateLvyouMemberContact->getDataByRule($param);

        $data['update_time'] = time();
        $data['status']      = -1;


        MemberContactModel::startTrans();
        try {
            $res = MemberContactModel::update($data);
            MemberContactModel::commit();
        } catch (Exception $exception) {

            MemberContactModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }


    /**
     * 联系人列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param $param
     * @param $uid
     * @param int $pageinate
     * @return mixed
     */
    public function getContactList($param, $uid, $pageinate = 10)
    {
        $params              = $this->validateLvyouMemberContact->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $condition['uid']    = $uid;
        $fields              = "*";
        return $this->modelLvyouMemberContact->getList($condition, $fields, '', $pageinate);
    }

    /**
     * 联系人详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 22:18
     * @param $param
     * @return mixed
     */
    public function getContactDetail($param)
    {
        $this->validateLvyouMemberContact->goCheck('del');
        $params              = $this->validateLvyouMemberContact->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['id'] = $params['id'];

        $fields = "*";
        $info   = $this->modelLvyouMemberContact->getInfo($condition, $fields);
        return $info;
    }
}