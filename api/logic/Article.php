<?php

namespace app\api\logic;

use app\api\error\Article as ArticleError;
use app\api\error\CodeBase;
use app\api\model\ArticleClass;
use app\common\logic\Article as CommonArticle;

/**
 * 文章接口逻辑
 */
class Article extends ApiBase
{

    public static $commonArticleLogic = null;


    /**
     * 获取文章分类列表
     */
    public function getArticleCategoryList($param = [])
    {

        $where = [];

        $condition['status'] = 1;
        //文章条件
        if($param['is_bottom'] == 1){
            $where['is_bottom'] = 1;
        }
//        $condition['name'] = ['like',"%文章%"];
//        $lists = ArticleClass::where($condition)->with(['article'])->field('id,name')->order('sort asc')->select();
        $lists = ArticleClass::where($condition)->with(['article'=>function($query) use ($where){
            $query->where($where);
        }])->select();

        return $lists;
    }

    /**
     * 获取文章列表
     */
    public function getArticleList($param = [])
    {
        if (empty($param['class_id'])) {
            return CodeBase::$idIsNull;
        }
        $where['status'] = 1;
        $where['class_id'] = $param['class_id'];
        $field             = 'id,status,class_id,title,content,img,create_time';

        $list = $this->modelArticle->getList($where, $field, 'sort desc,create_time desc', false, [], '');

        return $list;
    }

    /**
     * 获取文章信息
     */
    public function getArticleDetail($param = [])
    {
        if (empty($param['id'])) {
            return CodeBase::$idIsNull;
        }
        $where['id'] = $param['id'];

        $field = 'id,title,sub_title,content,img,create_time,read_num';

        $info = $this->modelArticle->getInfo($where, $field);
        if (empty($info)) {
            $info = [];
        }
//        $info['content'] = html_entity_decode($info['content'] );

        return $info;
    }

    /**
     * 获取单页
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 23:54
     */
    public function getArticlePageDetail($flag)
    {
        $where['flag'] = $flag;
        $field         = 'id,title,sub_title,content,img,create_time,read_num';

        $info = $this->modelArticle->getInfo($where, $field);
        if (empty($info)) {
            $info = [];
        }

        return $info;
    }

    /**
     * noticedata 0:读取商城公告  1：手动填写
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getNoticeList($data = [], $field = 'id,title,create_time')
    {
        if (!$this->validateNotice->scene('first')->check($data)) {
            return ArticleError::noticeError('1030001', $this->validateNotice->getError());
        }
        $where['uniacid'] = config('uniacid');
        $where['status']  = 1;
        $list             = $this->modelNotice->getList($where, $field, 'sort desc,create_time desc', false, [], '', $data['noticenum']);

        return $list;
    }

    /**
     * 公告详情
     * @notice_id 公告id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getNoticeInfo($data = [])
    {
        if (empty($data['notice_id'])) return ArticleError::noticeError('1030001', '公告id必填');

        $info = $this->modelNotice->getInfo(['uniacid' => \config('uniacid'), 'status' => 1, 'id' => $data['notice_id']], 'id,title,content');

        return $info;
    }


    /**
     * 友情链接
     */
    public function getBlogrollList()
    {

        $field = "id,name,img,url,describe";
        return $this->modelArticleBlogroll->getList([DATA_STATUS_NAME => DATA_NORMAL], $field, 'sort desc,id asc', false);
    }



}
