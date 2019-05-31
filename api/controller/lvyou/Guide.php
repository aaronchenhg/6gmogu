<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 18:41
 * Copyright in Highnes
 */

namespace app\api\controller\lvyou;
use app\api\logic\Token;

use app\api\controller\ApiBase;

/**
 *  游玩策略/攻略
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\controller\lvyou
 */
class Guide extends ApiBase
{


    /**
     * index
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function index()
    {
        $data = $this->param;
        $data['type'] = 'list';
        $data = $this->logicLvyouGuide->getGuideList($data);
        return $this->apiReturn($data);
    }


    /**
     * 相关文章
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @return mixed
     */
    public function getGuideListNext()
    {
        $data = $this->logicLvyouGuide->getGuideListNext($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 获取用户发表的攻略
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:47
     * @return mixed
     */
    public function listByUid()
    {
        $data = $this->param;
        $data['type'] = 'mylist';
        $data = $this->logicLvyouGuide->getGuideList($data, Token::getCurrentUid());
        return $this->apiReturn($data);
    }


    /**
     * 攻略详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function detail()
    {
        $data = $this->logicLvyouGuide->getGuideDetail($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 推荐攻略
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function recommend()
    {
        $this->param['recommend'] = 1;
        $data                     = $this->logicLvyouGuide->getGuideList($this->param);
        return $this->apiReturn($data);
    }

    /**
     * 攻略评论列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function comments()
    {
        $data = $this->logicLvyouGuide->getGuideCommentList($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 发布攻略（精彩回顾）
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function publishGuide()
    {
        $data = $this->logicLvyouGuide->publishGuide($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 评论攻略
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:28
     * @return mixed
     */
    public function publishCommentGuide()
    {
        $data = $this->logicLvyouGuide->publishCommentGuide($this->param, Token::getCurrentUid());
        return $this->apiReturn($data);
    }

    /**
     * 回复评论
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:55
     * @return mixed
     */
    public function replyCcomment()
    {
        $data = $this->logicLvyouGuide->replyCommentGuide($this->param, Token::getCurrentUid());
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
        $data = $this->logicMemberFavorite->addFavorite($this->param, Token::getCurrentUid(), 20);
        return $this->apiReturn($data);
    }

}