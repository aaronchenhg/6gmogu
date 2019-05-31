<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/6 23:22
 * Copyright in Highnes
 */

namespace app\api\logic;


class MemberLevel extends ApiBase
{

    /**
     * 会员等级列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function getMemberLevelList()
    {

        $data = \app\admin\model\MemberLevel::where(['status' => 1])->field("level,levelname,id,ordermoney")->select();
        return $data;
    }
}