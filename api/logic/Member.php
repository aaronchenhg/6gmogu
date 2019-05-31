<?php

namespace app\api\logic;

use app\common\logic\Article as CommonArticle;
use app\api\error\Member as MemberError;
use app\api\error\CodeBase;
use think\Db;
use think\Exception;
use think\Log;

/**
 * 文章接口逻辑
 */
class Member extends ApiBase
{

    public static $commonArticleLogic = null;

    /**
     * 基类初始化
     */
    public function __construct()
    {
        // 执行父类构造方法
        parent::__construct();

        empty(static::$commonArticleLogic) && (static::$commonArticleLogic = get_sington_object('Article', CommonArticle::class));
    }

    /**
     * 获取会员信息
     */
    public function getMemberInfo($user_id)
    {
        $integral = $this->modelMember->where('id',$user_id)->field('total_integral')->find();
        $levelInfo = Db::name('MemberLevel')->where('ordermoney','elt',$integral['total_integral'])
            ->field('id,ordermoney,levelname,discount')
            ->order('ordermoney desc')
            ->find();
        if(!empty($levelInfo)){
            $res = Db::name('Member')->where('id',$user_id)->update(['level_id' => $levelInfo['id']]);
        }

        $this->modelMember->alias('m');
        $where = [
            'm.id' => $user_id,
        ];
        $join  = [
            ['MemberLevel ml', 'ml.id=m.level_id', 'left'],
        ];
        $field = 'm.nickname,m.headimgurl,m.sex,m.city,m.mobile,m.birthday,ml.levelname,ml.level,integral,total_integral,realname,email';

        $info = $this->modelMember->getInfo($where, $field, $join);
        if (!empty($info)) {
            $info['favorite_num']    = $this->modelMemberFavorite->where('status', '=', 1)->where('user_id', $user_id)->count();//收藏数量
            $info['history_num']     = $this->modelMemberHistory->where('status', '=', 1)->where('user_id', $user_id)->count();//足迹数量
            $info['order_num_one']   = $this->modelOrder->where('order_status', '=', 1)->where('user_id', $user_id)->count();//待付款订单数量
            $info['order_num_two']   = $this->modelOrder->where('order_status', '=', 2)->where('user_id', $user_id)->count();//待发货订单数量
            $info['order_num_three'] = $this->modelOrder->where('order_status', '=', 3)->where('user_id', $user_id)->count();//待收货订单数量
            $info['order_num']       = $this->modelOrder->where('order_status', 'neq', -1)->where('user_id', $user_id)->count();//总订单数量
        }
        if (empty($info)) {
            return CodeBase::$failure;
        }
        //浏览日志
        insertAccessStatist(1,4,isMobile());
        return $info;
    }

    /**
     * 更新会员信息
     * @param $data 数据
     * @return mixed
     */
    public function updataMemberInfo($user_id, $param)
    {

        $validate_result = $this->validateMember->scene('updateInfo')->check($param);

        if (!$validate_result) {

            return MemberError::usernameOrPasswordEmpty('5050001', $this->validateMember->getError());
        }

        $data['id'] = $user_id;
        if ($param['field'] == 'birthday') {
            $param['value'] = strtotime($param['value']);
        }

        $data[$param['field']] = $param['value'];
        $res                   = $this->modelMember->setInfo($data);
        if ($res) {
            return CodeBase::$success;
        } else {
            return CodeBase::$failure;
        }
    }


    /**
     * 修改会员信息
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/7 14:47
     * @param $user_id 用户id
     * @param $param 修改数据
     * @return array
     */
    public function updateMember($user_id, $param)
    {
        $param = $this->validateMemberInfo->getDatabyRule($param);

        $data = array_filter($param);
        if (empty($data)) {
            return $data;
        }
        if (array_key_exists('birthday', $data)) {
            $data['birthday'] = strtotime($data['birthday']);
        }
        $data['id']          = $user_id;
        $data['update_time'] = time();
        $res                 = $this->modelMember->setInfo($data);

        return ['res' => $res];

    }


    /**
     * 获取会员地址列表
     * @param $param
     */
    public function getMemberAddressList($user_id, $param)
    {

//        $this->modelArticle->alias('a');

        $where['user_id']        = $user_id;
        $where[DATA_STATUS_NAME] = 1;
        $field                   = ['id,username,mobile,address,is_default'];

        return $this->modelMemberAddress->getList($where, $field, '', 5);
    }

    /**
     * 获取会员地址详细
     * @param $id 地址ID
     */
    public function getMemberAddressDetailed($id)
    {
        if (empty($id)) {
            return MemberError::$idNull;
        }
        $where['id'] = $id;
        $field       = 'id,username,mobile,province,city,county,address,is_default';
        $info        = $this->modelMemberAddress->getInfo($where, $field);

//        $info['address'] = getCityName($info['province']['province']).getCityName($info['city']['city']).getCityName($info['county']['county']).$info['address'];

        return $info;

    }

    /**
     * 添加会员地址
     * @param $param
     */
    public function addMemberAddress($user_id, $param)
    {
        $validate_result = $this->validateMemberAddress->scene('add')->check($param);
        if (!$validate_result) {
            return MemberError::usernameOrPasswordEmpty('5050001', $this->validateMemberAddress->getError());
        }

        $where  = [
            'user_id' => $user_id,
            'status'  => 1,
        ];
        $isNull = $this->modelMemberAddress->where($where)->field('id')->select();
        //如果数据库没有地址则添加第一条为默认地址
        empty($isNull) ? $param['is_default'] = 1 : $param['is_default'] = 2;

        $param['uniacid'] = 1;
        $param['user_id'] = $user_id;
//        $res =  $this->modelMemberAddress->setInfo($param);
        unset($param['version']);
        $res = $this->modelMemberAddress->insertGetId($param);
        if ($res) {
            return ['id' => $res];
        } else {
            return CodeBase::$failure;
        }
    }

    /**
     * 编辑会员地址
     * @param $param
     */
    public function editMemberAddress($param)
    {
        $validate_result = $this->validateMemberAddress->scene('edit')->check($param);

        if (!$validate_result) {
            return MemberError::usernameOrPasswordEmpty('5050001', $this->validateMemberAddress->getError());
        }
        unset($param['version']);
        $res = $this->modelMemberAddress->setInfo($param);
        if ($res) {
            return CodeBase::$success;
        } else {
            return CodeBase::$failure;
        }
    }

    /**
     * 设置会员默认地址
     * @param $param
     */
    public function setDefaultAddress($user_id, $param)
    {
        //数据验证
        $validate_result = $this->validateMemberAddress->scene('del')->check($param);
        if (!$validate_result) {
            return MemberError::usernameOrPasswordEmpty('5050001', $this->validateMemberAddress->getError());
        }
        $data['update_time'] = time();
        $data['is_default']  = 1;
        Db::startTrans();
        try {
            //更新默认地址
            $this->modelMemberAddress->where('id', $param['id'])->update($data);
            //把其它地址改为非默认
            $this->modelMemberAddress->where(['id' => ['neq', $param['id']], 'user_id' => $user_id])->update(['is_default' => 2, 'update_time' => time()]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 删除地址（改变状态假删除）
     * @param $param
     */
    public function delMemberAddress($param)
    {

        $validate_result = $this->validateMemberAddress->scene('del')->check($param);

        if (!$validate_result) {
            return MemberError::usernameOrPasswordEmpty('5050001', $this->validateMemberAddress->getError());
        }
        $param['status'] = -1;
        unset($param['version']);
        $res = $this->modelMemberAddress->setInfo($param);
        if ($res) {
            return CodeBase::$success;
        } else {
            return CodeBase::$failure;
        }
    }

    /**
     * 获取会员收藏列表
     * @param $param
     * @return mixed
     */
    public function getMemberFavoriteList($user_id, $param)
    {

        $this->modelMemberFavorite->alias('a');
        //条件
        $where['user_id']  = $user_id;
        $where['a.status'] = 1;
        $join              = [
            ['goods g', 'g.id=a.goods_id', 'left']
        ];
//        $field = ['a.id,a.create_time,a.goods_id,g.title,g.thumb,g.sub_title,g.market_price'];

        $field = ['a.id,a.create_time,a.goods_id,g.title,IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("' . Config('setting.site_url') . '",g.thumb))as thumb,g.sub_title,g.market_price'];
        return $this->modelMemberFavorite->getList($where, $field, 'create_time desc', 8, $join);
    }

    /**
     * 收藏/取消商品
     * @param $goods_id 商品ID
     */
    public function setGoodsFavorite($user_id, $goods_id)
    {
        if (empty($goods_id)) {
            return MemberError::$idNull;
        }
        $status = $this->modelMemberFavorite->where(['goods_id' => $goods_id, 'user_id' => $user_id])->value('status');
        Db::startTrans();
        try {
            if ($status == 1) {
                //如果商品已收藏则取消收藏
                $this->modelMemberFavorite->where(['goods_id' => $goods_id, 'user_id' => $user_id])->update(['status' => 2, 'update_time' => time()]);
            } elseif ($status == 2) {
                //重新收藏商品
                $this->modelMemberFavorite->where(['goods_id' => $goods_id, 'user_id' => $user_id])->update(['status' => 1, 'update_time' => time()]);
            } else {
                //未收藏过该商品添加新数据
                $data['user_id']     = $user_id;
                $data['goods_id']    = $goods_id;
                $data['type']        = 1;
                $data['uniacid']     = 1;
                $data['status']      = 1;
                $data['create_time'] = time();
                //如果未收藏
                $this->modelMemberFavorite->insert($data);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            halt($e->getMessage());
            return CodeBase::$failure;
        }
        return CodeBase::$success;

    }

    /**
     * 取消商品收藏
     * @param $ids 收藏ID(多个)
     */
    public function cancelGoodsFavorite($user_id, $ids)
    {
        if (empty($ids)) {
            return MemberError::$idNull;
        }
        $where['id']      = ['in', $ids];
        $where['user_id'] = $user_id;
        $res              = $this->modelMemberFavorite->where($where)->update(['status' => 2, 'update_time' => time()]);
        if (!$res) {
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 获取会员足迹列表
     * @param $user_id 会员ID
     */
    public function getMemberHistoryList($user_id)
    {

        $this->modelMemberHistory->alias('mh');

        $where['mh.user_id'] = $user_id;
        $where['mh.status']  = 1;
        $join                = [
            ['goods g', 'g.id=mh.goods_id', 'left']
        ];
        $field               = ['mh.id,mh.goods_id,IF(g.thumb = "","",IF(LOCATE("http", g.thumb) > 0,g.thumb,CONCAT("' . Config('setting.site_url') . '",g.thumb)))as thumb,g.title,g.sub_title,g.market_price'];

        $list = $this->modelMemberHistory->getList($where, $field, 'mh.create_time desc', 6, $join);

        return $list;

    }

    /**
     * 删除足迹
     * @param $ids 足迹ID (可多个)
     */
    public function deleteMemberHistory($user_id, $ids)
    {

        if (empty($ids)) {
            return CodeBase::$idIsNull;
        }
        $where['id']      = ['in', $ids];
        $where['user_id'] = $user_id;

        $data['status']      = -1;
        $data['update_time'] = time();

        $res = $this->modelMemberHistory->where($where)->update($data);

        return $res ? CodeBase::$success : CodeBase::$failure;
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     * @author: chenhg <945076855@qq.com>
     * @param $openid openid
     * @param $access_token 访问access_token
     * @return $this
     */
    public function getUserinfoByOpenid($openid, $access_token)
    {
        //获取普通access_token
//        $this->wxLoginUrl = sprintf(config('wx.access_token_url_wx'), config('wx.appid'), config('wx.appsecret'));
//        $result = https_request($this->wxLoginUrl);
//        $accessToken = json_decode($result, true);

        //获取用户基本信息
        $get_userinfo_url = sprintf(config('wx.get_userinfo_url'), $access_token, $openid);
        $result           = https_request($get_userinfo_url);
        $wxResult         = json_decode($result, true);
        $fail             = array_key_exists('errcode', $wxResult);

        if ($fail) {
            return CodeBase::$failure;
        }
        //验证unionid是否存在
        if (isset($wxResult['unionid'])) {
            $chcke = $this->modelMember->getInfo(['unionid' => @$wxResult['unionid']]);
        } else {
            $chcke = $this->modelMember->getInfo(['openid' => $wxResult['openid']]);
        }

        if (!$chcke) {
            // 创建用户信息
            $wxResult['nickname_code'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $wxResult['nickname']); // 过滤到有符号的昵称
            $wxResult['integral']      = 0;
            $wxResult['from_type']     = 1; //来源类型 （1.公众号 2.小程序）
            $wxResult['create_time']   = time();


            $wxResult['id']    = $this->modelMember->insertGetId($wxResult);
            $wxResult['token'] =  Token::saveToCache($wxResult); //$this->logicCommon->tokenSign($wxResult);

        } else {
            if (empty($chcke['openid'])) {
                $this->modelMember->where('id', $chcke['id'])->update(['openid' => $openid]);
            }

//            $wxResult['token1'] = $this->logicCommon->tokenSign($chcke);
            $wxResult['token']  = Token::saveToCache($chcke);
        }

//        cache('from_type', 1);
        return $wxResult;
    }


    public function userExistByUnionid($unionid)
    {
        $user_id = $this->modelMember->where('unionid', 'eq', $unionid)->value('id');
        return $user_id ? $user_id : false;
    }

    public function userExistByOpenid($openid)
    {
        $user_id = $this->modelMember->where('openid', 'eq', $openid)->value('id');
        return $user_id ? $user_id : false;
    }

    /**
     * 优惠券领券中心
     */
    public function getCouponCenterList()
    {
        $this->modelCoupon->alias('c');

        $where['c.status']   = 1;
        $where['c.get_type'] = 1;

        $field = ['id,coupon_name,enough,time_limit,time_days,time_start,time_end,discount,deduct,back_type,title_color,total'];

        $list = $this->modelCoupon->getList($where, $field, 'c.create_time desc', 6);

        return $list;

    }

    /**
     * 用户领取优惠券
     * @param $user_id 用户ID
     * @param $id 优惠券ID
     */
    public function receiveCoupon($user_id, $id)
    {
        if (empty($id)) {
            return CodeBase::$idIsNull;
        }
        $where['id'] = $id;
        //优惠券数据
        $couponInfo = $this->modelCoupon->where($where)->field('id,get_type,status,time_limit,time_days,total,get_max')->find();

        if ($couponInfo['get_type'] != 1 || $couponInfo['status'] != 1) {
            return CodeBase::$failure;
        }
        //判断是否已经领取完
        if ($couponInfo['total'] <= 0) {
            return MemberError::$CouponNull;
        }
        //判断用户是否已经超过最大领取数
        $receiveWhere['user_id']   = $user_id;
        $receiveWhere['coupon_id'] = $id;
        //会员领券数
        $receiveInfo = $this->modelCouponData->where($receiveWhere)->count();
        if ($receiveInfo >= $couponInfo['get_max']) {
            return MemberError::$maximum;
        }
        Db::startTrans();
        try {
            //组装数据
            $data['user_id']   = $user_id;
            $data['coupon_id'] = $id;
            $data['get_type']  = 2;
            $data['used']      = 1;
            $data['get_time']  = time();
            //过期时间
            if ($couponInfo['time_limit'] == 1 && $couponInfo['time_days'] > 0) {
                $data['end_time'] = strtotime("+" . $couponInfo['time_days'] . " day");
            }
            //添加数据
            $this->modelCouponData->insert($data);
            //减少优惠券数量
            $this->modelCoupon->where($where)->setDec('total', 1);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return CodeBase::$failure;
        }

        return CodeBase::$success;
    }

    /**
     * 会员优惠券列表
     */
    public function memberCouponList($user_id, $type)
    {

        $this->modelCouponData->alias('cd');
        $where['cd.user_id'] = $user_id;
        //是否使用或过期
        if ($type == 1) {
            $where['c.time_start'] = ['<=', time()];
            $where['cd.used']      = 1;
        } elseif ($type == 2) {
            $where['c.time_end'] = ['>', time()];
            $where['cd.used']    = 2;
        }
        $join = [
            ['coupon c', 'c.id=cd.coupon_id', 'left']
        ];

        $field = ['c.id,cd.coupon_id,c.coupon_name,c.enough,c.time_limit,c.time_days,c.time_start,
        c.time_end,cd.end_time,c.discount,c.deduct,c.back_type,c.title_color,cd.used,cd.end_time'];

        $list = $this->modelCouponData->getList($where, $field, 'c.create_time desc', 6, $join);
        if (empty($list)) {
            return CodeBase::$failure;
        }
        foreach ($list as $key => $val) {
            $list[$key]['time_start'] = date('Y-m-d', $val['time_start']);
            $list[$key]['time_end']   = date('Y-m-d', $val['time_end']);
        }

        return $list;
    }

    /**
     * 会员优惠券列表
     */
    public function memberCouponList0821($user_id)
    {

        $this->modelCouponData->alias('cd');
        $where['cd.user_id'] = $user_id;

        $join = [
            ['coupon c', 'c.id=cd.coupon_id', 'left']
        ];

        $field = ['c.id,cd.coupon_id,c.coupon_name,c.enough,c.time_limit,c.time_days,c.time_start,
        c.time_end,c.discount,c.deduct,c.back_type,c.title_color,cd.used,cd.end_time'];

        $list = $this->modelCouponData->getList($where, $field, 'c.create_time desc', 6, $join);
        if (empty($list)) {
            return CodeBase::$failure;
        }
        foreach ($list as $key => $val) {
            $list[$key]['isOverdue'] = 0;
            if ($val['time_limit'] == 1 && $val['end_time'] < date('Y-m-d')) {
                $list[$key]['isOverdue'] = 1;
            } elseif ($val['time_limit'] == 2 && $val['time_end'] < time()) {
                $list[$key]['isOverdue'] = 1;
            }

            $list[$key]['time_start'] = date('Y-m-d', $val['time_start']);
            $list[$key]['time_end']   = date('Y-m-d', $val['time_end']);
        }

        return $list;
    }


    /**
     * @param $user_id 用户ID
     * @param $formid
     * @param $type 1表示支付环境,2表示表单提交环境
     */
    public function saveFormid($user_id, $formid, $type)
    {

        if (empty($formid)) {
            return CodeBase::$idIsNull;
        }

        $savedata = [
            'form_id'     => $formid,
            'times'       => $type == 1 ? 3 : 1,
            'user_id'     => $user_id,
            'create_time' => time(),
            'pass_time'   => strtotime('+7 day')
        ];
        //添加数据
        $result = $this->modelTemplateMessageFromeid->insert($savedata);

        if (!$result) {
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }

    /**
     * 修改模板消息发送次数
     * @param $userid
     * @param $formid
     * @return int|true
     * @throws \think\Exception
     */
    public function setNumFormid($formid)
    {
        $result = $this->modelTemplateMessageFromeid->where(['form_id' => $formid])->setDec('times', 1);
        return $result;
    }

    /**
     * 获取一条可用的formid
     * @param $userid
     * @return mixed
     */
    public function getCanUseFormid($userid)
    {
        $formid = $this->modelTemplateMessageFromeid->where(['user_id' => $userid, 'times' => ['>', 0], 'pass_time' => ['>', time()]])
            ->limit(1)
            ->value('form_id');
        return $formid;
    }

}


