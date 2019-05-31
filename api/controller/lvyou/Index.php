<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/5 15:48
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;


use app\api\controller\ApiBase;
use app\common\controller\ControllerBase;

class Index extends ApiBase
{

    /**
     * banner列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function banner()
    {
        $data = $this->logicIndex->getBannerList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 导航菜单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     */
    public function navMenu()
    {
        $data = $this->logicIndex->getBannerList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 公告列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 20:11
     * @return mixed
     */
    public function notice()
    {
        $data = $this->logicIndex->getNoticeList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 公告详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 20:11
     * @return mixed
     */
    public function getNoticeDetail()
    {
        $data = $this->logicIndex->getNoticeDetail($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 搜索记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 20:11
     * @return mixed
     */
    public function historySearch()
    {
        $data = $this->logicLvyouLine->getHistorySearch($this->param, $this->user_id);
        return $this->apiReturn($data);
    }

    /**
     * 删除搜索记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 20:11
     * @return mixed
     */
    public function delHistorySearch()
    {
        $data = $this->logicLvyouLine->delHistorySearch($this->param, $this->user_id);
        return $this->apiReturn($data);
    }
}