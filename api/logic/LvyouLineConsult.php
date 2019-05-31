<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/27 20:19
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use  \app\common\model\LvyouLineConsult as LineConsultModel;

class LvyouLineConsult extends ApiBase
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
    public function addConsult($param, $uid = 0)
    {
        // 验证参数是否正确
        $this->validateLvyouLineConsult->goCheck();
        // 添加参数到数据
        $data = $this->validateLvyouLineConsult->getDataByRule($param);

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
        $data['title']       = \app\common\model\LvyouLine::where('id', 'eq', $data['line_id'])->value("title");
        $data['uid']         = $uid;
        $data['nickname']    = \app\common\model\Member::where('id', 'eq', $uid)->value('nickname_code');


        LineConsultModel::startTrans();
        try {
            $res = LineConsultModel::create($data);
            LineConsultModel::commit();
        } catch (Exception $exception) {

            LineConsultModel::rollback();
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
    public function getLineConsult($param, $uid = 0, $pageinate = 2)
    {
        $params              = $this->validateLvyouLineConsult->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $uid && $condition['uid'] = $uid;
        $params['line_id'] && $condition['line_id'] = $params['line_id'];
        $fields = "id,title,nickname,content,reply_content,create_time,reply_time,uid,line_id";
        return $this->modelLvyouLineConsult->getList($condition, $fields, '', $pageinate);

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
        $params              = $this->validateLvyouLineConsult->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['id'] = $params['id'];
        $fields = "*";
        $info   = $this->modelLvyouLineConsult->getInfo($condition, $fields);
        return $info;
    }
}