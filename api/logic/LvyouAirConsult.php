<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/27 20:19
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use  \app\common\model\LvyouAirConsult as AirConsultModel;

class LvyouAirConsult extends ApiBase
{
    /**
     * 机票咨询
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 19:47
     * @param $param 表单字段
     * @return $this
     * @throws ForbiddenException
     */
    public function addConsult($param,$uid)
    {
        // 验证参数是否正确
        $this->validateLvyouAirConsult->goCheck();
        // 添加参数到数据
        $data = $this->validateLvyouAirConsult->getDataByRule($param);

        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');

        $sensitiveLexicon = explode(',',$configInfo['value']);

        $isSensitive = sensitiveLexiconJudge($param['content'],$sensitiveLexicon);

        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        unset($data['id']);
        $data['status']      = 2;
        $data['create_time'] = time();
        $data['uid'] = $uid;


        AirConsultModel::startTrans();
        try {
            $res = AirConsultModel::create($data);
            AirConsultModel::commit();
        } catch (Exception $exception) {

            AirConsultModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }


    /**
     * getAirConsult
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:28
     * @param $param
     * @param int $uid
     * @param int $pageinate
     * @return mixed
     */
    public function getAirConsult($param, $uid = 0, $pageinate = 2)
    {
//        $params              = $this->validateLvyouAirConsult->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $condition['uid']    = $uid;
        $fields              = "id,type,start_city,reach_city,start_date,people_number,child_number,remark,contact_phone,create_time,air_space";
        return $this->modelLvyouAirConsult->getList($condition, $fields, '', $pageinate);

    }

    /**
     * 咨询详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 22:28
     * @param $param
     * @return mixed
     */
    public function getConsultDetail($param)
    {
        $params              = $this->validateLvyouAirConsult->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['id'] = $params['id'];
        $fields = "*";
        $info   = $this->modelLvyouAirConsult->getInfo($condition, $fields);
        return $info;
    }
}