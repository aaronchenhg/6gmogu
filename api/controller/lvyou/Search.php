<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;

class Search extends ApiBase
{
    public function searchOld()
    {
        return $this->apiReturn($this->logicSearch->getSearchLists($this->user_id,$this->param));
    }
}