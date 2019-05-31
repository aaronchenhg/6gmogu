<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8 0008
 * Time: 18:39
 */

namespace app\api\logic;

use think\Config;
use think\Db;

class Duobao extends ApiBase
{
    public function getDuobaoImg($where = [],$field = '*')
    {
        return $this->modelDuobaoCategrouy->getList($where,$field,'',false);
    }
    /**
     * 查询活动列表
     * User: 李姣
     * @param $uniacid 联合ID
     * @param array $data
     * @return mixed
     * Date: 2018/08/14
     */
    public function getItemList($uniacid, $data = [])
    {
        $where['b.uniacid'] = $uniacid;
        $where['a.status'] = $where['a.is_start'] = $where['b.status'] = 1;
        $where['a.has_lottery'] = 0;
        isset($data['id']) && $where['b.duobao_cate_id'] = $data['id'];

        $join = [
            ['duobao b', 'b.id = a.duobao_id']
        ];

        $field = 'a.id,b.name,a.max_buy,a.current_buy,a.has_lottery,a.unit_price,a.origin_price,a.deal_gallery,a.current_schedule_now,a.start_type,a.start_time,b.duobao_cate_id';

        $this->modelDuobaoItem->alias('a');

        $list = $this->modelDuobaoItem->getList($where, $field, 'b.is_recomend desc,a.id desc', $data['page'], $join);

        return $list;
    }

    /**
     * 查询活动详情
     * User: 李姣
     * @param array $data
     * @return mixed
     * Date: 2018/08/14
     */
    public function getItemInfo($data = [])
    {
        $where = [];
        (isset($data['id']) && !empty($data['id'])) && $where['a.id'] = $data['id'];

        $join = [
            ['duobao b', 'b.id = a.duobao_id'],
            ['goods g', 'a.goods_id = g.id'],
            ['goods_option go', 'a.spec_id = go.id', 'LEFT']
        ];
        $field = 'a.id,b.name,a.max_buy,a.current_buy,a.deal_gallery,
        a.current_schedule_now,b.content,g.content as goods_content,b.max_schedule,a.goods_id,go.title as option_name,a.spec_id,a.user_max_buy,a.min_buy,a.unit_price,a.origin_price';

        $this->modelDuobaoItem->alias('a');

        $info = $this->modelDuobaoItem->getInfo($where, $field, $join);

        $this->modelDuobaoItem->query("UPDATE shop_duobao_item SET click_count = click_count + 1 WHERE id = " . $data['id']);

        $info['jilu'] = $this->itemjilu($info['id']);

        return $info;
    }

    /**
     * 参与活动记录列表
     * User: 李姣
     * @param $item_id 活动ID
     * @return mixed
     * Date: 2018/08/14
     */
    private function itemjilu($item_id)
    {
        $this->modelDuobaoItemLog->alias('a');

        $join = [
            ['member m', 'a.user_id = m.id'],
            ['duobao_order_item b', 'a.order_item_id = b.id']
        ];

        $field = 'a.show_sn,m.nickname,b.create_time';

        $list = $this->modelDuobaoItemLog->getList(['a.duobao_item_id' => $item_id,'a.is_buy'=>1], $field, 'b.id desc', false, $join);
        return $list;
    }

    /**
     * 指定用户参与记录
     * User: 李姣
     * @param $userid 用户ID
     * @param $data
     * @return mixed
     * Date: 2018/08/14
     */
    public function getUserLogList($userid, $data)
    {
        $this->modelDuobaoItemLog->alias('a');
        $join = [
            ['member m', 'a.user_id = m.id'],
            ['duobao_order_item b', 'a.order_item_id = b.id'],
            ['duobao_item c', 'c.id = b.duobao_item_id'],
            ['duobao_order e', 'b.order_id = e.id'],
            ['order o', 'o.duobao_order_id = e.id','LEFT'],
        ];

        $field = 'a.show_sn,m.nickname,b.create_time,c.name,b.is_luck,c.has_lottery,c.current_schedule_now,b.order_sn,e.pay_amount,
        IF((o.order_status=2 || o.order_status=3 || o.order_status=4 || o.order_status=1),"1","0") AS is_ling';

        $list = $this->modelDuobaoItemLog->getList(['a.user_id' => $userid,'a.is_buy'=>1], $field, 'b.id desc', 10, $join);
//        print_r($list);exit;
        return $list;
    }

    /**
     * 查询商品是否有正在进行中的夺宝活动
     * User: 李姣
     * @param $goods_id 商品ID
     * Date: 2018/08/14
     */
    public function getIsDuobaoing($goods_id)
    {
        $where['a.goods_id'] = $goods_id;
        $where['a.is_start'] = $where['a.status'] = 1;

        $this->modelDuobaoItem->alias('a');

        $list = $this->modelDuobaoItem->getInfo($where,'a.id',[]);

        if(empty($list))
        {
            $is_duobao['is_duobao'] = $is_duobao['duobao_item_id'] = 0;
        }else
        {
            $is_duobao['is_duobao'] = 1;
            $is_duobao['duobao_item_id'] = $list['id'];
        }

        return $is_duobao;
    }
}