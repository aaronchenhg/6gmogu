<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/6 23:33
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\model\MemberFavorite as MemberFavoriteModel;
use app\lib\exception\ForbiddenException;

class MemberFavorite extends ApiBase
{


    /**
     * 添加收藏
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 0:00
     * @param $param
     * @param $uid 用户id
     * @param int $type 类型 （1：商品，10：线路，20：攻略）
     * @return $this
     * @throws ForbiddenException
     */
    public function addFavorite($param, $uid, $type = 10)
    {
        $this->validateMemberFavorite->goCheck();
        $params = $this->validateMemberFavorite->getDataByRule($param);
        $id     = MemberFavoriteModel::where(['goods_id' => $params['goods_id'], 'type' => $type,'user_id' => $uid])->value('id');
        if ($id) {
            throw new ForbiddenException(['msg' => '已经收藏过']);
        }
        unset($params['id']);
        $params['create_time'] = time();
        $params['user_id']     = $uid;
        $params['type']        = $type;
        $res                   = MemberFavoriteModel::create($params);
        return $res;
    }


    /**
     * 收藏列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 0:14
     * @param $type 类型 （1：商品，10：线路，20：攻略）
     * @param $uid 用户ID
     * @return array
     */
    public function getFavoriteList($param, $uid)
    {
        $type = @$param['type'] ?: 10;

        $_data = [];
        switch ($type) {
            case 10: // 线路
                $_data = $this->getFavoriteListOfLine($uid);
                break;
            case 20:  // 攻略
                $_data = $this->getFavoriteListOfGuide($uid);
                break;
            case 1:
                // 商品 :TODO
                $_data = $this->getFavoriteListOfGoods($uid);
                break;
        }
        return $_data;
    }


    public function getFavoriteListOfLine($uid = 0)
    {
        $this->modelMemberFavorite->alias('a');
        //条件
        $where['user_id']  = $uid;
        $where['a.type']   = 10;
        $where['a.status'] = 1;
        $join              = [
            ['lvyou_line line', 'line.id=a.goods_id', 'left'],
            ['lvyou_start_city city', 'line.start_city=city.id', 'left'],
            ['lvyou_reach_city reach', 'line.reach_city=reach.id', 'left'],
        ];

        $field = ['a.id,a.create_time,city.name start_city,reach.name reach_city,a.goods_id,IF(LOCATE("http", line.image) > 0,line.image,CONCAT("' . Config('setting.site_url') . '",line.image))as image,line.title,line.sub_title,line.price,line.days,line.sale'];
        return $this->modelMemberFavorite->getList($where, $field, 'a.id desc', DB_LIST_ROWS, $join);
    }

    public function getFavoriteListOfGuide($uid = 0)
    {
        $this->modelMemberFavorite->alias('a');
        //条件
        $where['user_id']  = $uid;
        $where['a.type']   = 20;
        $where['a.status'] = 1;
        $join              = [
            ['lvyou_guide guide', 'guide.id=a.goods_id', 'left']
        ];

        $field = ['a.id,a.create_time,a.goods_id,IF(LOCATE("http", guide.image) > 0,guide.image,CONCAT("' . Config('setting.site_url') . '",guide.image))as image,guide.title,guide.sub_title,is_new,is_hot,guide.comments,guide.views'];
        return $this->modelMemberFavorite->getList($where, $field, 'a.id desc', DB_LIST_ROWS, $join);
    }

    public function getFavoriteListOfGoods($uid = 0)
    {
        $this->modelMemberFavorite->alias('a');
        //条件
        $where['user_id']  = $uid;
        $where['a.type']   = 1;
        $where['a.status'] = 1;
        $join              = [
            ['goods g', 'g.id=a.goods_id', 'left']
        ];

        $field = ['a.id,a.create_time,a.goods_id,g.title,IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("' . Config('setting.site_url') . '",g.thumb))as thumb,g.sub_title,g.market_price'];
        return $this->modelMemberFavorite->getList($where, $field, 'create_time desc', DB_LIST_ROWS, $join);
    }

    /**
     * 删除收藏
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/9 16:46
     * @param $param
     * @param int $uid
     * @return int
     */
    public function deleteFavorite($param, $uid = 0)
    {
        $where['id'] = @$param['id'];
        $res = \app\api\model\MemberFavorite::destroy($where);
        return [$res];
    }

}