<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8 0008
 * Time: 17:31
 */

namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use app\api\logic\Token;
use think\Config;
use app\api\error\Duobao as DuobaoError;
use think\controller\Yar;
use think\Db;

class Duobao extends ApiBase
{
    public function __construct()
    {
        parent::__construct();

        $this->logicDuobaoCommon->startDuobaoPrize();
        $this->user_id = Token::getCurrentUid();
    }

    /**
     * User: 李姣
     * @return mixed
     * Date: 2018/08
     */
    public function banner()
    {
        $where['status'] = 1;
        isset($this->param['id']) && $where['id'] = $this->param['id'];

        $field = "id,name,IF(deal_gallery = '','',IF(LOCATE('http',deal_gallery) > 0,deal_gallery,CONCAT('" . Config::get('http_name') . "/',deal_gallery))) as url";
        $list = $this->logicDuobao->getDuobaoImg($where, $field);
        return $this->apiReturn($list);
    }
    public function itemList()
    {
        return $this->apiReturn($this->logicDuobao->getItemList(Config::get('uniacid'), $this->param));
    }

    public function itemInfo()
    {
        return $this->apiReturn($this->logicDuobao->getItemInfo($this->param));
    }
    /**
     * 获取参与记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     */
    public function itemLogList()
    {
        return $this->apiReturn($this->logicDuobao->getUserLogList($this->user_id, $this->param));
    }
}