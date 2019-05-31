<?php

namespace app\api\controller\lvyou;

use app\api\controller\ApiBase;

/**
 * 聚合接口控制器
 */
class Combination extends ApiBase
{

    /**
     * 首页接口
     */
    public function index()
    {

        $article_category_list = $this->logicArticle->getArticleCategoryList();
        $article_list = $this->logicArticle->getArticleList($this->param);

        return $this->apiReturn(compact('article_category_list', 'article_list'));
    }

    /**
     * 详情接口
     */
    public function details()
    {

        $article_category_list = $this->logicArticle->getArticleCategoryList();
        $article_details = $this->logicArticle->getArticleInfo($this->param);

        return $this->apiReturn(compact('article_category_list', 'article_details'));
    }
}
