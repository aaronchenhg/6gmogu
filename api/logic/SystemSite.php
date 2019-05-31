<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\logic;

class SystemSite extends ApiBase
{

    /**
     * 获取系统基础配置
     * @param array $where
     * @param bool $field
     * @return mixed
     */
    public function getSettingInfo($where = [], $field = true)
    {

        $info = $this->modelSystemSite->getInfo($where);

        return $info;
    }

}