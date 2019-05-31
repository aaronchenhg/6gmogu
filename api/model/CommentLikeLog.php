<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\model;


class CommentLikeLog extends ApiBase
{


    /**
     * 添加评论点赞记录
     * @param $from_user_id
     * @param $to_user_id
     * @param $type
     * @param $entity_id
     */
    public function insertCommetnLikeLog($from_user_id,$to_user_id,$type,$entity_id){

        $data['from_user_id'] = $from_user_id;
        $data['to_user_id'] = $to_user_id;
        $data['type'] = $type;
        $data['entity_id'] = $entity_id;
        $data['create_time'] = time();
        $data['update_time'] = time();

        return self::insert($data);

    }


}