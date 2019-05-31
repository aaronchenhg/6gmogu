<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 17:51
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use app\common\model\LvyouLineCustom as LvyouLineCustomModel;
use app\lib\exception\ForbiddenException;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\exception\ThrowableError;


/**
 * 定制线路
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\logic
 */
class LvyouLineCustom extends ApiBase
{
    /**
     * 定制线路
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 19:47
     * @param $param 表单字段
     * @return $this
     * @throws ForbiddenException
     */
    public function addCustom($param, $uid = 0)
    {
        // 验证参数是否正确
        $this->validateLvyouCustom->goCheck();
        // 添加参数到数据
        $data = $this->validateLvyouCustom->getDataByRule($param);

        unset($data['id']);
        $data['status']      = 2;
        $data['create_time'] = time();
        $data['uid']         = $uid;


        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');

        $sensitiveLexicon = explode(',',$configInfo['value']);

        $isSensitive = sensitiveLexiconJudge($param['content'],$sensitiveLexicon);

        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        LvyouLineCustomModel::startTrans();
        try {
            $res = LvyouLineCustomModel::create($data);
            LvyouLineCustomModel::commit();
        } catch (Exception $exception) {

            LvyouLineCustomModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }


    public function getCustomList($param, $uid, $pageinate = 10)
    {
        $params              = $this->validateLvyouCustom->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $condition['uid']    = $uid;
        $fields              = "id,type,start_city,reach_city,start_date,days,people_number,child_number,remark,contact_name,contact_phone,sex,email,address,create_time,create_time";
        return $this->modelLvyouLineCustom->getList($condition, $fields, '', $pageinate);
    }

    /**
     * 旅游线路详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 22:18
     * @param $param
     * @return mixed
     */
    public function getCustomDetail($param)
    {
        $params              = $this->validateLvyouCustom->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['id'] = $params['id'];

        $fields = "id,type,start_city,reach_city,start_date,days,people_number,child_number,remark,contact_name,contact_phone,sex,email,address,create_time,create_time";
        $info   = $this->modelLvyouLineCustom->getInfo($condition, $fields);
        return $info;
    }
}