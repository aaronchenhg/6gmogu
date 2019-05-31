<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;

class Search extends ApiBase
{
    public function __construct()
    {
        parent::__construct();

        $this->user_id = Token::getCurrentUid();
    }

    public function searchOld()
    {
        return $this->apiReturn($this->logicSearch->getSearchLists($this->user_id,$this->param));
    }
}