<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/14 0014
 */


namespace app\api\logic;
use app\api\error\Goods as GoodsError;
use think\Config;

class Goods extends ApiBase
{
    /**
     * 查询商品分类及分类层级
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getCategoryList()
    {
        //商品分类层级
        $cate_level = $this->logicCommon->getGoodsLevel();

        //商品分类
        $cate_where['uniacid'] = config('uniacid');
        $cate_where['status'] = $cate_where['enabled'] = 1;

        ($cate_level['level'] > 0) && $cate_where['level'] = ['between',[1,$cate_level['level']]];

        $cate_list = $this->modelGoodsCategory->getList($cate_where,'id,parent_id,IF(thumb = "","",IF(LOCATE("http", thumb) > 0,thumb,CONCAT("'.Config::get('http_name').'/'.'",thumb)))as thumb,description,level,name,"0" as is_chenked','sort desc,create_time desc',false);

        $cate['level'] = $cate_level;
        $cate['lists'] = list_to_tree($cate_list,'id','parent_id');

        return $cate;
    }

    /**
     * 查询商品列表
     * @keywords 搜索关键词
     * @ccates 商品分类
     * @goodssort 商品列表排序方式
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsLists($data = [],$pageinate = 12)
    {
        $where['g.status'] = 1;

        !isset($data['goodssort']) && $data['goodssort'] = 0;

        !empty($data['keywords']) && $where['g.title|g.content|g.short_title|g.isdiscount_title|g.sub_title|gc.name'] = ['like',"%".$data['keywords']."%"];
        !empty($data['cates']) && $where['g.cates'] = $data['cates'];
        !empty($data['is_send_free']) && $where['g.is_send_free'] = $data['is_send_free'];

        if(empty($data['min_prices']) && !empty($data['max_prices']))
        {
            $where['g.min_price'] = ['LT',$data['max_prices']];
        }
        elseif (!empty($data['min_prices']) && empty($data['max_prices']))
        {
            $where['g.min_price'] = ['GT',$data['min_prices']];
        }
        elseif (!empty($data['min_prices']) && !empty($data['max_prices']))
        {
            $where['g.min_price'] = ['between',[$data['min_prices'],$data['max_prices']]];
        }

        $field = 'g.short_title,g.id,g.title,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config::get('http_name').'/'.'",g.thumb)))as thumb,g.commission_thumb,
        IF(g.has_option = 1,g.min_price,g.market_price) as market_price,g.product_price,g.sales,IF(g.is_new = 1,"新品",g.is_new) as is_new,
        IF(g.is_hot = 1,"热卖",g.is_hot) as is_hot,IF(g.is_discount = 1,"促销",g.is_discount) as is_discount,IF(g.is_recommand = 1,"推荐",g.is_recommand) as is_recommand
        ,IF(g.is_send_free = 1,"包邮",g.is_send_free) as is_send_free,IF(g.is_time = 1,"限时卖",g.is_time) as is_time,g.is_show_sales,gc.name';

        $this->modelGoods->alias('g');

        //排序方式
        switch ($data['goodssort'])
        {
            case 1://按销量
                $order = 'g.sales desc,g.sort desc';
                break;
            case 2://价格降序
                $order = 'g.market_price desc,g.sort desc';
                break;
            case 3://价格升序
                $order = 'g.market_price asc,g.sort desc';
                break;
            case 0://综合
            default:
                $order = 'g.sort desc,g.create_time asc';
                break;
        }
        $join = [
            ['goods_category gc','gc.id=g.cates']
        ];

        $list = $this->modelGoods->getList($where,$field,$order,$pageinate,$join);
//        !empty($list) && $list = json_decode(str_replace('"thumb":"','"thumb":"'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/',json_encode($list)),true);

        return $list;
    }

    /**
     * 查询商品详情
     * @user_id 用户id
     * @goods_id 商品id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsDetail($data = [])
    {

        if(isMobile()){
           $user_id = Token::getCurrentUid();
        }else{
            $user_id = 0;
        }

        if(empty($data['goods_id'])) return GoodsError::$goodsInfoNull;

        $field = 'g.id,g.title,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("'.Config::get('http_name').'/'.'",g.thumb)))as thumb,
        g.commission_thumb,
        g.market_price,g.product_price,g.sales,IF(g.is_new = 1,"新品",g.is_new) as is_new,
        IF(g.is_hot = 1,"热卖",g.is_hot) as is_hot,IF(g.is_discount = 1,"促销",g.is_discount) as is_discount,IF(g.is_recommand = 1,"推荐",g.is_recommand) as is_recommand
        ,IF(g.is_send_free = 1,"包邮",g.is_send_free) as is_send_free,IF(g.is_time = 1,"限时卖",g.is_time) as is_time,g.is_show_sales,g.has_option,g.content,g.sales,
        IF(g.seven = 1,"7天无理由退换",g.seven) as seven,IF(g.repair = 1,"保修",g.repair) as repair,IF(g.quality = 1,"正品保证",g.quality) as quality,
        g.cash,g.invoice,g.is_dispatch_price,g.dispatch_price,g.short_title,g.isdiscount_title,g.isdiscount_time,g.min_buy,g.max_buy,g.total,g.repair,g.buy_content,g.groups_type,g.video,
        g.is_status_time,g.status_time_start,g.status_time_end,g.no_search,g.is_show_sales,g.shopid,g.buy_show,g.buy_content,g.is_comment,g.thumb_url,g.total,g.min_price,g.max_price';

        $this->modelGoods->alias('g');

        $info = $this->modelGoods->getInfo(['id'=>$data['goods_id']],$field);

        if(empty($info)) return GoodsError::$goodsInfoNull;
        //插入会员浏览足迹
        isInsertHistory($user_id,$data['goods_id']);
//        if(!empty($info['thumb_url']))
//        {
//
//            $info['thumb_url'] = implode(',',$info['thumb_url']);
//
//            $info['thumb_url'] = getImageUrl($info['thumb_url']);
//        }
        !empty($info['commission_thumb']) && $info['commission_thumb'] = getImageUrl($info['commission_thumb']);

        //购买后可见
        $info['buy_show'] == 1 && $info['is_buy'] = $this->getBuyShow($info,$user_id);

        //商品库存
        if($info['has_option'] == 1)
        {
            $spec = $this->getGoodsSpec(['goods_id'=>$info['id']]);

            if(!empty($spec) && !isset($spec['code']))
            {
                $spec = collection($spec['goods_spec'])->toArray();
                $info['total'] = array_sum(array_column($spec,'stock'));
            }else{
                $info['total'] = 0;
            }
        }

        //商品参数
        !empty($info) && $info['param_lists'] = $this->getGoodsParam($info);

        //是否收藏
        $info['is_collection'] = $this->getIsCollection($info['id'],$user_id);

        //是否有正在进行中的夺宝活动
//        $duobao = $this->logicDuobao->getIsDuobaoing($info['id']);
//        $info['is_duobao_ing'] = $duobao['is_duobao'];
//        $info['duobao_id'] = $duobao['duobao_item_id'];

        return $info;
    }

    /**
     * 用户是否收藏商品
     * @goods_id 商品id
     * @user_id 用户id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function getIsCollection($goods_id,$user_id)
    {
        $collection = $this->modelMemberFavorite->getInfo(['goods_id'=>$goods_id,'user_id'=>$user_id,'status'=>1]);

        empty($collection) ? $is_collection = 0 : $is_collection = 1 ;

        return $is_collection;
    }
    /**
     * 获取商品参数
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function getGoodsParam($info)
    {
        return $this->modelGoodsParam->getList(['goods_id'=>$info['id'],'status'=>1],'id,title,value,sort','sort desc,create_time desc',false);
    }

    /**
     * 用户是否购买过改商品
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function getBuyShow($info,$user_id)
    {
        $is_buy = 0;
        //购买后可见
        if($info['buy_show'] == 1)
        {
            $buy_where['o.user_id'] = $user_id;
            $buy_where['og.goods_id'] = $info['id'];
            $buy_where['o.order_status'] = 4;//已完成订单

            $this->modelOrder->alias('o');

            $buy_join = [
                ['order_goods og','o.id=og.order_id']
            ];

            !empty($this->modelOrder->getInfo($buy_where,'o.id',$buy_join)) && $is_buy = 1;
        }
        return $is_buy;
    }
    /**
     * 为你推荐商品列表
     * @userid 用户id
     * @recommand_num 推荐商品个数
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsRecommand($userid,$recommand_num,$data = [])
    {
        $where['is_recommand'] = 1;
        $where['status'] = 1;

        $goods_ids = $this->modelMemberHistory->getColumn(['user_id'=>$userid,'status'=>1],'goods_id');

        if((count($goods_ids) - $recommand_num) < 0)
        {
            $list_col = $this->modelGoods->getColumn(['is_recommand'=>1,'status'=>1,'id'=>['not in',$goods_ids]],'id');
            $goods_ids = array_merge($goods_ids,$list_col);
        }
        if((count($goods_ids) - $recommand_num) > 0)
        {
            $goods_ids = array_slice($goods_ids,0,$recommand_num);
        }

        $where['id'] = ['in',$goods_ids];
        $field = 'id,title,commission_thumb,sub_title,
        IF(thumb = "","",IF(LOCATE("http", thumb) > 0,thumb,CONCAT("'.Config::get('http_name').'/'.'",thumb)))as thumb,
        market_price,product_price,sales,IF(is_new = 1,"新品",is_new) as is_new,
        IF(is_hot = 1,"热卖",is_hot) as is_hot,IF(is_discount = 1,"促销",is_discount) as is_discount,IF(is_recommand = 1,"推荐",is_recommand) as is_recommand
        ,IF(is_send_free = 1,"包邮",is_send_free) as is_send_free,IF(is_time = 1,"限时卖",is_time) as is_time,is_show_sales';
        $lists_array = $this->modelGoods->getList($where,$field,'',false);

        return $lists_array;
    }

    /**
     * 单个商品规格列表
     * @goods_id 商品id
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsSpec($data = [])
    {
        if (empty($data['goods_id'])) return GoodsError::$goodsInfoNull;
        $spec_info = $this->modelGoods->getInfo(['id' => $data['goods_id']], 'has_option,spec');

        if ($spec_info['has_option'] != 1) return GoodsError::$goodsNoSpecNull;

        if (empty($spec_info['spec'])) return GoodsError::$goodsNumNull;

        $goods_option_info['goods_spec'] = $this->modelGoodsOption->getList(['goods_id' => $data['goods_id']], 'IF(thumb = "","",IF(LOCATE("http",thumb) > 0,thumb,CONCAT("'.Config::get('http_name').'/",thumb))) as thumb,specs,id,stock,title,product_price,market_price', 'sort desc,sales desc', false);//attr_value_items

        $option = json_decode($spec_info['spec'], true);

        foreach ($option as $k=>$value)
        {
            if(!empty($value['value']))
            {
                foreach ($value['value'] as $key_son=>$value_son)
                {
                    $option[$k]['value'][$key_son]['checked'] = 0;
                }
            }
        }
        $goods_option_info['spec_info_list'] = $option;

//        //规格排列组合
//        $goods_option_info['spec_stock'] = treatmentSpec($goods_option_info);

        return $goods_option_info;
    }
     /** 获取商品库存
     * @param $goods_id 商品ID
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getGoodsStock($goods_id){

        $info = $this->modelGoods->getInfo(['id'=>$goods_id],'total');

        return $info;
    }
    public function getSpecStock($data = [])
    {
        $info = $this->getGoodsSpec($data);

        //规格排列组合
        $spec_stock = treatmentSpec($info);

        return $spec_stock;
    }
}