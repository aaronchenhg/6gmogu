<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/13 0013
 */


namespace app\api\controller\v1;

use app\api\controller\ApiBase;
use think\Config;

class Wxapp extends ApiBase
{
    /**
     * 首页排版
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function firstApp()
    {
        return $this->apiReturn($this->logicWxapp->getWxappPageInfo($this->param));
    }

    /**
     * 商品手机商品搜索
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function goodsList()
    {
        return $this->apiReturn($this->logicWxapp->getFirstGoodsList($this->param));
    }

    /**
     * 底部菜单
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function footerApp()
    {
        return $this->apiReturn($this->logicWxapp->getFooterApp($this->param));
    }

    public function memberApp()
    {
        return $this->apiReturn($this->logicWxapp->getWxappPageInfo(['type'=>3]));
    }

    /**
     * 启动页广告
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function startadv()
    {
        return $this->apiReturn($this->logicWxapp->getStartadv(['status'=>1,'uniacid'=>Config::get('uniacid')]));
    }
}