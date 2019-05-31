<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 20:52
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use \app\common\model\LvyouGuide as LvyouGuideModel;
use app\common\model\LvyouGuideComment;
use app\common\model\LvyouSearchHistory;

class LvyouGuide extends ApiBase
{

    /**
     *  用户发布攻略
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 19:45
     * @param $param
     * @param $uid
     * @return $this
     */
    public function publishGuide($param, $uid)
    {
        // 验证参数是否正确
        $this->validateLvyouGuide->goCheck();
        // 添加参数到数据
        $data = $this->validateLvyouGuide->getDataByRule($param);
        unset($data['id']);
        $data                 = array_filter($data);
        $data['status']       = 2;
        $data['create_time']  = time();
        $data['source']       = 1;
        $data['uid']          = $uid;
        $data['publish_time'] = date("Y-m-d H:i:s");


        LvyouGuideModel::startTrans();
        try {
            $res = LvyouGuideModel::create($data);
            LvyouGuideModel::commit();
        } catch (Exception $exception) {

            LvyouGuideModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }


    /**
     * 攻略列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 21:15
     * @param $param
     * @param string $order
     * @param int|mixed $pageinate
     * @return mixed
     */
    public function getGuideList($param, $uid = 0, $order = 'id desc,create_time desc', $pageinate = DB_LIST_ROWS)
    {

        $params = $this->validateLvyouGuide->getDataByRule($param);
        if ($params['status']) {
            $condition['status'] = $params['status'];
        } else {
            if($param['type'] == 'list'){
            $condition['status'] = 1;
            }else{
            $condition['status'] = ['neq', -1];
            }
        }
        $uid && $condition['uid'] = $uid;
        $params['category'] && $condition['category'] = $params['category'] == '研学策略' ? '研学攻略' : $params['category'];
        $params['keywords'] && $condition['title|sub_title'] = ['like', "%{$params['keywords']}%"];

        // :TODO 用户id  手机端记录搜索信息
        if(isMobile()){
            if ($params['keywords']) {
                LvyouSearchHistory::addSearchRecord($params['keywords'], 2, Token::getCurrentUid());
            }
        }


        if ($params['pagesize']) {
            $pageinate = $params['pagesize'];
        }

        $fields = "id,title,sub_title,content,image,is_new,is_hot,tags,comments,publish_time,author,category,source,views";
        return $this->modelLvyouGuide->getList($condition, $fields, $order, $pageinate);
    }


    /**
     * getGuideListNext
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/10 13:21
     */
    public function getGuideListNext($param)
    {
        $params              = $this->validateLvyouGuide->getDataByRule($param);
        $condition           = [];
        $condition['status'] = 1;
        $params['category'] && $condition['category'] = $params['category'];

        $id            = $params['id'];
        $_data['pre']  = \app\common\model\LvyouGuide::where($condition)->where('id', 'lt', $id)->order('id desc')->field("id,title")->find();
        $_data['next'] = \app\common\model\LvyouGuide::where($condition)->where('id', 'gt', $id)->order('id asc')->field("id,title")->find();
        return $_data;
    }


    /**
     * 旅游攻略详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 21:15
     * @param $param
     * @return mixed
     */
    public function getGuideDetail($param)
    {
        $params              = $this->validateLvyouGuide->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['id'] = $params['id'];
        $fields = "*";
        \app\common\model\LvyouGuide::where($condition)->setInc('views', 1);
        $info = $this->modelLvyouGuide->getInfo($condition, $fields);
        return $info;
    }

    /**
     * 评论攻略
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/27 19:49
     * @param $param
     * @param $uid
     * @return $this
     */
    public function publishCommentGuide($param, $uid)
    {
        // 验证参数是否正确
        $this->validateLvyouGuideComment->goCheck('comment');
        // 添加参数到数据
        $data = $this->validateLvyouGuideComment->getDataByRule($param);

        //判断敏感词汇
        $configInfo= $this->modelConfig->getInfo(['name' => 'sensitive_lexicon'],'id,value');

        $sensitiveLexicon = explode(',',$configInfo['value']);

        $isSensitive = sensitiveLexiconJudge($param['content'],$sensitiveLexicon);

        if($isSensitive){
            return CodeBase::errorMessage(500001,'输入的内容包含敏感词汇');
        }

        unset($data['id']);
        $data                = array_filter($data);
        $data['status']      = 2;
        $data['create_time'] = time();
        $data['uid']         = $uid;
        $data['nickname']    = \app\common\model\Member::where('id', $uid)->value('nickname');
        $data['headimgurl']  = \app\common\model\Member::where('id', $uid)->value('headimgurl');

        LvyouGuideComment::startTrans();
        try {
            $res = LvyouGuideComment::create($data);
            LvyouGuideComment::commit();
        } catch (Exception $exception) {

            LvyouGuideComment::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;

    }

    /**
     * 回复攻略评论
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:55
     * @param $param
     * @param $uid
     * @return $this
     */
    public function replyCommentGuide($param, $uid)
    {
        // 验证参数是否正确
        $this->validateLvyouGuideComment->goCheck('reply');
        // 添加参数到数据
        $data = $this->validateLvyouGuideComment->getDataByRule($param);
        unset($data['id']);
        $data['status']      = 2;
        $data['create_time'] = time();
        $data['uid']         = $uid;
        $data['reply_uid']   = $uid;
        $data['nickname']    = \app\common\model\Member::where('id', $uid)->value('nickname');
        $data['headimgurl']  = \app\common\model\Member::where('id', $uid)->value('headimgurl');

        LvyouGuideComment::startTrans();
        try {
            $res = LvyouGuideComment::create($data);
            LvyouGuideComment::commit();
        } catch (Exception $exception) {

            LvyouGuideComment::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 攻略评论列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/26 0:59
     * @param $param
     * @param $uid
     * @return $this
     */
    public function getGuideCommentList($param, $uid = 0)
    {
        $params              = $this->validateLvyouGuideComment->getDataByRule($param);
        $condition['status'] = ['neq', -1];
        $params['id'] && $condition['guide_id'] = $params['id'];

        $lists = LvyouGuideComment::where($condition)->select();

//        $lists = list_to_tree($lists, 'id', 'reply_id ');
        return $lists;
    }
}