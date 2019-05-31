<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/13 0013
 */


namespace app\api\logic;

use think\Cache;
use app\common\logic\Wxapp as CommonWxapp;
use app\api\error\Wxapp as WxappError;
use think\Config;

class Wxapp extends ApiBase
{
    /**
     * 获取页面框架
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getWxappPageInfo($data = [],$form = 'miniApps')
    {
        if(isset($data['id']) && !empty($data['id']))
        {
            $where['id'] = $data['id'];
        }else
        {
            (!isset($data['type']) || empty($data['type'])) && $data['type'] = 2;

            $where['type'] = $data['type'];
            $where['is_default'] = $where['status'] = 1;
            $where['uniacid'] = config('uniacid');
        }

        if(empty($data['type'])){
            $data['type'] = 1;
        }

        $info = Cache::get($data['type'].'wxapppage'.getChangeNumber());

        if(empty($info))
        {
            $field = 'id,name,data';

            $info = $this->getPageInfo($where,$field,$data['type'],$form);

            if(!empty($info['data']))
            {
                $data_info = base64_decode($info['data']);
                $str = Config::get('http_name');

                $data_info = str_replace(['"imgurl":"\/upload','"thumb":"\/static','"iconurl":"\/static','"imgurl":"\/static','"thumb":"\/upload','"leftnavimg":"\/upload','"rightnavimg":"\/upload','"poster":"\/upload','"videourl":"\/upload'],
                    ['"imgurl":"'.$str.'/upload','"thumb":"'.$str.'/static','"iconurl":"'.$str.'/static','"imgurl":"'.$str.'/static','"thumb":"'.$str.'/upload','"leftnavimg":"'.$str.'/upload','"rightnavimg":"'.$str.'/upload','"poster":"'.$str.'/upload','"videourl":"'.$str.'/upload'],$data_info);

                $info['data'] = json_decode($data_info,true);
            }

            $info['is_change'] = getChangeNumber();
            Cache::set($data['type'].'wxapppage'.$info['is_change'],$info);
        }

        !isset($info['data']) ? $array_lists = [] : ($data['type'] != 30 ? $array_lists = $this->loadData($info) : $array_lists = $info);

        return $array_lists;
    }
    private function getPageInfo($where,$field,$type,$form)
    {
        if($form == 'miniApps')
        {
            return $this->modelWxappPage->getInfo($where,$field,null,null,true,'wxapp_'.$type);
        }else
        {
            return $this->modelWxappDiypage->getInfo($where,$field,null,null,true,'wxapp_diypage_'.$type);
        }
    }
    /**
     * 首页界面加载后台数据
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function loadData($info)
    {
        $array = $info['data']['items'];
        if(!empty($array))
        {
            foreach ($array as $k=>$v)
            {
                if($v['id'] == 'notice' && $v['params']['noticedata'] != 1)
                {
                    $array[$k]['data'] = $this->logicArticle->getNoticeList(['noticedata'=>0,'noticenum'=>$v['params']['noticenum']],'id,title,"" as linkurl');
                }
                if($v['id'] == 'goods')
                {
                    unset($array[$k]['data']);
                    if(isset($v['data']) && !empty($v['data']))
                    {
                        $goods_list = $this->getFirstGoodsList(['goodsdata'=>$v['params']['goodsdata'],'cateid'=>$v['params']['cateid'],'goodssort'=>$v['params']['goodssort'],'goodsnum'=>$v['params']['goodsnum'],'data'=>$v['data']]);
                    }else
                    {
                        $goods_list = [];
                    }

                    $array[$k]['data'] = $goods_list;
                }
            }
        }
        @$info['data']['items'] = $array;

        return $info;
    }

    /**
     * 首页商品列表信息
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getFirstGoodsList($data = [])
    {
//        if(!$this->validateGoods->scene('first')->check($data))
//        {
//            return WxappError::getGoodsError('1050001',$this->validateGoods->getError());
//        }
        $http = Config::get('http_name');

        $field = 'g.id as gid,g.title,IF(g.thumb = "",g.thumb,CONCAT("'.$http.'/'.'",g.thumb)) as thumb,
        g.commission_thumb,
        (CASE WHEN (g.has_option = 1) THEN g.min_price ELSE g.market_price END) AS price
        ,g.product_price as productprice,g.sales,IF(g.is_new = 1,"新品",g.is_new) as is_new,
        IF(g.is_hot = 1,"热卖",g.is_hot) as is_hot,IF(g.is_discount = 1,"促销",g.is_discount) as is_discount,IF(g.is_recommand = 1,"推荐",g.is_recommand) as is_recommand
        ,IF(g.is_send_free = 1,"包邮",g.is_send_free) as is_send_free,IF(g.is_time = 1,"限时卖",g.is_time) as is_time,g.is_show_sales,g.cates as ctype';

        $where['g.status'] = $where['g.is_show_sales'] = 1;
        $where['g.uniacid'] = config('uniacid');
        $join = [];
        switch ($data['goodsdata'])
        {
            case '1'://分类
                $where['g.cates'] = $data['cateid'];
                $where['gc.status'] = 1;
                $join = [
                    ['goods_category gc','g.cates = gc.id']
                ];
                break;
            case '2'://分组
                $cate_ids = $this->modelGoodsGroup->getValue(['id'=>$data['cateid'],'status'=>1],'goods_ids');

                $where['gc.id'] = ['in',explode(',',$cate_ids)];

                $join = [
                    ['goods_category gc','g.cates = gc.id'],
                ];
                break;
            case '3'://新品
                $where['g.is_new'] = 1;
                break;
            case '4'://热卖
                $where['g.is_hot'] = 1;
                break;
            case '5'://推荐商品
                $where['g.is_recommand'] = 1;
                break;
            case '6'://促销
                $where['g.is_discount'] = 1;
                break;
            case '7'://包邮
                $where['g.is_send_free'] = 1;
                break;
            case '8'://限时卖商品
                $where['g.is_time'] = 1;
                $where['g.time_start'] = ['<=',time()];
                $where['g.time_end'] = ['>=',time()];
                break;
            default:
                $ids = array_column($data['data'],'gid');
                $where['g.id'] = ['in',$ids];
                break;
        }
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
        $this->modelGoods->alias('g');

        $list = $this->modelGoods->getList($where,$field,$order,false,$join,'',$data['goodsnum']);

        return $list;
    }

    /**
     * 首页底部菜单接口
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getFooterApp($data = [])
    {
        $footer = Cache::get('footer');

        if(empty($footer))
        {
            $info = $this->modelCommonSysset->getValue(['uniacid'=>config('uniacid')],'sets',null,false,true,'footer');

            if(empty($info)) return WxappError::getGoodsError('1050001','商家还未配置');

            $info = unserialize($info);

            if(empty($info['app']['tabbar'])) return WxappError::getGoodsError('1050001','商家还未配置菜单');

            $footer = json_encode(unserialize($info['app']['tabbar']));
            $http = Config::get('http_name');

            $footer = str_replace(['"iconPath":"','"selectedIconPath":"'],['"iconPath":"'.$http.'/','"selectedIconPath":"'.$http.'/'],$footer);

            $footer = json_decode($footer,true);

            Cache::set('footer',$footer);
        }
        return $footer;
    }

    public function getStartadv($where = [])
    {
        $info = $this->modelWxappStartadv->getInfo($where);

        if(!empty($info) && !empty($info['data']))
        {
            $data_info = base64_decode($info['data']);
            $str = Config::get('http_name');

            $data_info = str_replace(['"imgurl":"\/upload','"thumb":"\/static','"iconurl":"\/static','"imgurl":"\/static','"thumb":"\/upload'],
                ['"imgurl":"'.$str.'/upload','"thumb":"'.$str.'/static','"iconurl":"'.$str.'/static','"imgurl":"'.$str.'/static','"thumb":"'.$str.'/upload'],$data_info);

            $info['data'] = json_decode($data_info,true);
        }
        return $info;
    }
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($sessionKey,$encryptedData, $iv, &$data )
    {
        if (strlen($sessionKey) != 24) {
            return WxappError::$IllegalAesKey;
        }
        $aesKey=base64_decode($sessionKey);


        if (strlen($iv) != 24) {
            return  WxappError::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

//        print_r($aesIV);exit;
        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );

        if( $dataObj  == NULL )
        {
            return WxappError::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != Config::get('appid') )
        {
            return WxappError::$IllegalBuffer;
        }
        $data = $result;

        if(empty(0)){
            return $dataObj->phoneNumber;
        }
    }

}