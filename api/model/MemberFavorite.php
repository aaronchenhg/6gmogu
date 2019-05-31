<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class MemberFavorite extends ApiBase
{

    /**
     * @param $id
     * @param $user_id 用户ID
     * @param $type 类型 （1:商品，10：线路，20：攻略)
     */
    public static function isFavorite($id,$user_id,$type){

        $where['goods_id'] = $id;
        $where['user_id'] = $user_id;
        $where['type'] = $type;

        $result = self::where($where)->value('id');

        return $result ? 1 : 2;

    }

}