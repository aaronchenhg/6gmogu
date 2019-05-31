<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/19 0019
 */
namespace app\api\logic;

use app\api\error\Search as SearchError;

class Search extends ApiBase
{
    /**
     * 查询搜索历史并更新
     * @user_id 用户id
     * @keywords 搜索关键词
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getSearchLists($userid,$data = [])
    {
        $where['status'] = 1;

        !empty($userid) && $where['user_id'] = $userid;
        !empty($data['keywords']) && $where['keywords'] = ['like',"%".$data['keywords']."%"];

        if(!empty($data['keywords']))
        {
            $info = $this->modelMemberSearch->getInfo($where);

            if(empty($info))
            {
                if(!$this->modelMemberSearch->addInfo(['keywords'=>$data['keywords'],'user_id'=>$userid,'status'=>1,'num'=>1,'uniacid'=>config('uniacid'),'create_time'=>time()]))
                    return SearchError::$addSearchError;
            }else
            {
                $sql = 'UPDATE shop_member_search SET num = num +1,update_time = '.time().' WHERE id='.$info['id'];
                if($this->modelMemberSearch->query($sql) === false)
                    return SearchError::$updateSearchError;
            }
        }

        $list = $this->modelMemberSearch->getList($where,'id,keywords,create_time,update_time,num','create_time desc,num desc',false);

        return $list;
    }

    /**
     * 获取热搜
     */
    public function getHotSearch(){

        $where['status'] = 1;

        $field = 'id,name,sort,status';

        $result = $this->modelGoodsHotSearch->getList($where,$field,'sort asc',false);

        return $result;

    }

}