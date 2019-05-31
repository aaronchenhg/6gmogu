<?php

namespace app\api\controller\v1;

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
        return $this->apiReturn($this->logicArticle->getArticleCategoryList());
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
    public function articleInfo()
    {
        return $this->apiReturn($this->logicArticle->getArticleInfo($this->param));
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
