<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;

/**
 * 文章接口控制器
 */
class Article extends ApiBase
{

    /**
     * 文章分类接口
     */
    public function categoryList()
    {
        $param = $this->param;
        $param['is_bottom'] = input('is_bottom',2);
        return $this->apiReturn($this->logicArticle->getArticleCategoryList($param));
    }

    /**
     * 文章列表接口
     */
    public function articleList()
    {
        return $this->apiReturn($this->logicArticle->getArticleList($this->param));
    }

    /**
     * 文章详细接口
     */
    public function articleDetail()
    {
        return $this->apiReturn($this->logicArticle->getArticleDetail($this->param));
    }


    /**
     * 关于我们(单页)
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 23:53
     * @return mixed
     */
    public function articlePage()
    {
        $flag = input('flag','about');
        return $this->apiReturn($this->logicArticle->getArticlePageDetail($flag));
    }
    public function friendList()
    {

        return $this->apiReturn($this->logicArticle->getBlogrollList());
    }




    /**
     * 公告列表
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function notice()
    {
        return $this->apiReturn($this->logicArticle->getNoticeList($this->param));
    }

    public function noticeDetails()
    {
        return $this->apiReturn($this->logicArticle->getNoticeInfo($this->param));
    }
}
