<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;
use app\api\logic\Token;

class Goods extends ApiBase
{
    /**
     * 获取商品分类及分类层级
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function category()
    {
        return $this->apiReturn($this->logicGoods->getCategoryList());
    }

    /**
     * 商品列表
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodslists()
    {
        return $this->apiReturn($this->logicGoods->getGoodsLists($this->param));
    }

    /**
     * 商品详情
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodsDetail()
    {
        return $this->apiReturn($this->logicGoods->getGoodsDetail(Token::getCurrentUid(),$this->param));
    }

    /**
     * 为你推荐
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function recommand()
    {
        return $this->apiReturn($this->logicGoods->getGoodsRecommand(Token::getCurrentUid(),$this->recommand_num,$this->param));
    }

    /**
     * 单个商品规格列表
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodsSpec()
    {
        return $this->apiReturn($this->logicGoods->getGoodsSpec($this->param));
    }
    public function specStock()
    {
        return $this->apiReturn($this->logicGoods->getSpecStock($this->param));
    }
}