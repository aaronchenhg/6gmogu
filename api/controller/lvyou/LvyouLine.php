<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 18:17
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;
use app\api\logic\Token;

use app\api\controller\ApiBase;

/**
 * 旅游线路接口
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\controller\lvyou
 */
class LvyouLine extends ApiBase
{

    /**
     * 线路列表
     * @params  reach_city,fit_crowd,theme,start_city
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 18:21
     * @return mixed
     */
    public function line()
    {

        $data = $this->logicLvyouLine->getLineList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 线路详情
     * @params  line_id
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @return mixed
     */
    public function lineDetail()
    {
        $param = $this->param;

//        $param['users_id'] = Token::getCurrentUid();

        $data = $this->logicLvyouLine->getLineDetail($param);
        return $this->apiReturn($data);
    }

    public function lineDate()
    {
        $data = $this->logicLvyouLine->getLineDateList($this->param);
        return $this->apiReturn($data);
    }


    /**
     * 线路分类
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 18:21
     * @return mixed
     */
    public function category()
    {
        $data = $this->logicLvyouLine->getLineCategory($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 筛选列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 18:24
     */
    public function screenList()
    {
        $data = $this->logicLvyouLine->getScreenList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 获取线路套餐
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:56
     * @return mixed
     */
    public function getLineSpecListById()
    {
        $data = $this->logicLvyouLine->getLineSpecListById($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 选择保险
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:57
     * @return mixed
     */
    public function getLineInsuranceListById()
    {
        $data = $this->logicLvyouLine->getLineInsuranceListById($this->param);
        return $this->apiReturn($data);
    }


    /**
     * 添加收藏
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 23:45
     * @return mixed
     */
    public function addFavorite()
    {
        $data = $this->logicMemberFavorite->addFavorite($this->param, Token::getCurrentUid(), 10);
        return $this->apiReturn($data);
    }


}