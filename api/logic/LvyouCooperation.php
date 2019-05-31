<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 19:32
 * Copyright in Highnes
 */

namespace app\api\logic;


use app\api\error\CodeBase;

class LvyouCooperation extends ApiBase
{

    /**
     * 产品合作
     * @param $param
     * @param $type （1.产品合作 2.基地合作 3.机构合作 4.代理人合作）
     * @return array
     */
    public function Connection($param,$type = 1){
        if(empty($type)){
            return CodeBase::errorMessage(10010,'缺少参数type');
        }
        if($type == 1){
            $scene = 'productConnection';
        }elseif ($type == 2){
            $scene = 'baseConnection';
        }elseif ($type == 3){
            $scene = 'organizationConnection';
        }elseif ($type == 4){
            $scene = 'agentConnection';
        }else{
            return CodeBase::errorMessage(10011,'type参数错误');
        }

        // 验证参数是否正确
        $this->validateLvyouCooperation->goCheck($scene);
        // 添加参数到数据
        $paramData = $this->validateLvyouCooperation->getDataByRule($param);

        $jsonData = json_encode($paramData);

        $data['type'] = $type;
        $data['create_time'] = time();
        $data['json_data'] = $jsonData;

        $result = $this->modelLvyouCooperationConnection->setInfo($data);
        if (!$result) {
            return CodeBase::$failure;
        } else {
            return CodeBase::$success;
        }
    }




}