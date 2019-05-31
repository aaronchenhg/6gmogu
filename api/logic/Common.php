<?php

namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\error\Common as CommonError;
use \Firebase\JWT\JWT;
use app\common\logic\District as DistrictLogic;
use think\Cache;
use think\Config;
use think\Log;
use think\Session;
use app\api\logic\Wxapp;
/**
 * 接口基础逻辑
 */
class Common extends ApiBase
{
    public static $District = null;

    /**
     * 登录接口逻辑
     */
    public function login($data = [])
    {

        $validate_result = $this->validateMember->scene('login')->check($data);
        
        if (!$validate_result) {
            
            return CommonError::$usernameOrPasswordEmpty;
        }
        begin:
        
        $member = $this->logicMember->getMemberInfo(['username' => $data['username']]);

        // 若不存在用户则注册
        if (empty($member))
        {
            $register_result = $this->register($data);
            
            if (!$register_result) {
                
                return CommonError::$registerFail;
            }
            
            goto begin;
        }
        
        if (data_md5_key($data['password']) !== $member['password']) {
            
            return CommonError::$passwordError;
        }
        
        return $this->tokenSign($member);
    }
    
    /**
     * 注册方法
     */
    public function register($data)
    {
        
        $data['nickname']  = $data['username'];
        $data['password']  = data_md5_key($data['password']);

        return $this->logicMember->setInfo($data);
    }
    
    /**
     * JWT验签方法
     */
    public static function tokenSign($member)
    {
        $key = API_KEY . JWT_KEY;
        
        $jwt_data = ['member_id' => $member['id'], 'nickname' => $member['nickname'], 'openid' => $member['openid'],'create_time'=>$member['create_time']];
        
        $token = [
            "iss"   => "OneBase JWT",         // 签发者
            "iat"   => TIME_NOW,              // 签发时间
            "exp"   => TIME_NOW + TIME_NOW,   // 过期时间
            "aud"   => 'HLS',             // 接收方
            "sub"   => 'HLS',             // 面向的用户
            "data"  => $jwt_data
        ];
        
        $jwt = JWT::encode($token, $key);
        
        $jwt_data['user_token'] = $jwt;
        
        return $jwt_data;
    }
    
    /**
     * 修改密码
     */
    public function changePassword($data)
    {
        
        $member = get_member_by_token($data['user_token']);
        
        $member_info = $this->logicMember->getMemberInfo(['id' => $member->member_id]);
        
        if (empty($data['old_password']) || empty($data['new_password'])) {
            
            return CommonError::$oldOrNewPassword;
        }
        
        if (data_md5_key($data['old_password']) !== $member_info['password']) {
            
            return CommonError::$passwordError;
        }

        $member_info['password'] = $data['new_password'];
        
        $result = $this->logicMember->setInfo($member_info);
        
        return $result ? CodeBase::$success : CommonError::$changePasswordFail;
    }
    


    /**
     * 省市区列表
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getDistrictList()
    {
        static::$District = get_sington_object('DistrictLogic', DistrictLogic::class);

        $list = Cache::get('area');

        if(empty($list))
        {
            $list['province'] = static::$District->getDistrict(['level'=>1,'status'=>1],'id,parent_id,name');
            $list['city'] = static::$District->getDistrict(['level'=>2,'status'=>1],'id,parent_id,name');
            $list['county'] = static::$District->getDistrict(['level'=>3,'status'=>1],'id,parent_id,name');

            $list['is_change'] = getChangeNumber();

            Cache::set('area',$list);
        }
        return $list;


    }

    /**
     * 商品筛选
     * @ccates 商品分类
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsSearch($data = [])
    {
        $where['status'] = $cate_where['status'] = 1;

        if(!empty($data['ccates']))
        {
            $where['cates'] = $data['ccates'];
            $cate_where['parent_id'] = $data['ccates'];
        }

        $cate_level = $this->getGoodsLevel();

        $cate_where['level'] = $cate_level['level'];
        $cate_where['uniacid'] = Config::get('uniacid');

        $cate_lists = $this->modelGoodsCategory->getList($cate_where,'id,name,level,parent_id','sort desc,id desc',false);

        $list[0]['title'] = '分类';
        $list[0]['field'] = 'cates';
        $list[0]['lists'] = $cate_lists;
        $list[1]['title'] = '包邮';
        $list[1]['field'] = 'is_send_free';
        $list[1]['lists'] = [0=>['id'=>1,'name'=>'包邮'],1=>['id'=>0,'name'=>'不包邮']];

        $price['title'] = '价格区间';
        $price['lists'] = $this->getGoodsPriceLevel($where);

        return ['lists'=>$list,'price'=>$price];
    }

    /**
     * 计算商品价格区间
     * @where 商品查询条件
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    private function getGoodsPriceLevel($where = [])
    {
        $field = 'MAX(IF(has_option = 1,max_price,market_price)) as goods_max_price,MIN(IF(has_option = 1,min_price,market_price)) as goods_min_price';

        $price_info = $this->modelGoods->getInfo($where,$field);//查询符合条件商品最低价和最高价

        $price_level = Config::get('price_level');//商品价格显示级数
        $price_gap = round(($price_info['goods_max_price'] - $price_info['goods_min_price']) / $price_level);//价格层级间距

        $price_lists = array();
        if(!empty($price_info['goods_max_price']) && !empty($price_info['goods_min_price']))
        {
            for ($i = 0;$i < $price_level ;$i++)
            {
                $price_lists[$i]['min_price'] = $price_info['goods_min_price'] + $price_gap * $i;
                $price_lists[$i]['max_price'] = $price_info['goods_min_price'] + $price_gap * ($i + 1);

                $price_lists[$i]['show_price'] = $price_lists[$i]['min_price'].'至'.$price_lists[$i]['max_price'];

                if($i == 0)
                {
                    $price_lists[$i]['min_price'] = 0;
                    $price_lists[$i]['show_price'] = $price_info['goods_min_price'] + $price_gap * ($i + 1).'以下';
                }
                if($i == $price_level - 1)
                {
                    $price_lists[$i]['max_price'] = 0;
                    $price_lists[$i]['min_price'] = $price_info['goods_max_price'];
                    $price_lists[$i]['show_price'] = $price_info['goods_max_price'].'以上';
                }
            }
        }
        return $price_lists;
    }
    /**
     * 查询商品层级显示
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function getGoodsLevel()
    {
        //商品分类层级
        $cate_level = $this->modelGoodsSysset->getValue(['uniacid'=>config('uniacid')],'sets');
        $cate_level = unserialize($cate_level)['category'];
        $cate_level['advimg'] = getImgSrc($cate_level['advimg']);
        return $cate_level;
    }

    /**
     * 小程序登录
     * @userInfo 用户信息
     * @code 前端微信登录返回code
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function wxappLogin($data = [])
    {
        if(empty($data)) return CommonError::$userInfoNull;
        if(empty($data['code'])) return CommonError::$userInfoNull;

        if(array_key_exists('errcode',$data)) return CommonError::$userInfoGetFail;

//        $url = sprintf(Config::get('login_url'),Config::get('appid'),Config::get('appsecret'),$data['code']);
        $url = sprintf(Config::get('login_url'),config('wx.appid_mini'),config('wx.appsecret_mini'),$data['code']);

        $wx_info = CurlPost($url);


        if(array_key_exists('errcode',$wx_info)) return CommonError::$getWxInfoFail;

        if(empty($wx_info['unionid'])){
            $wx_info['unionid'] = 0;
            $member_info = $this->modelMember->getInfo(['openid_mini'=>$wx_info['openid']]);
        }else{
            $member_info = $this->modelMember->getInfo(['unionid'=>$wx_info['unionid']]);
        }


        //用户第一次进入小程序
        if(empty($member_info))
        {
            $user_info = json_decode($data['user_info'],true);
//            halt($user_info);

            $member_info['openid_mini'] = $wx_info['openid'];
            $member_info['headimgurl'] = $user_info['avatarUrl'];
            $member_info['nickname'] = $user_info['nickName'];
            $member_info['nickname_code'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $user_info['nickName']);
//            $member_info['unionid'] = Config::get('unionid');
            $member_info['unionid'] = $wx_info['unionid'];

            $member_info['sex'] =  $user_info['gender'];
            $member_info['from_type'] = 2; //来源类型 （1.公众号 2.小程序）
            $member_info['create_time'] = time();
            $member_info['city'] = $user_info['city'];
            $member_info['province'] = $user_info['province'];
            $member_info['country'] = $user_info['country'];
            $member_info['language'] = $user_info['language'];
            $member_info['session_key'] = $wx_info['session_key'];

            $member_info['id'] = $this->modelMember->insertGetId($member_info);

            if(!$member_info['id']) return CommonError::$addUserWxInfoFail;

        }else
        {
            $this->modelMember->setInfo(['session_key'=>$wx_info['session_key'],'openid_mini' => $wx_info['openid']],['id'=>$member_info['id']]);
        }
        Session::set('user_id',$member_info['id']);

        cache('from_type',2);
        $member_info['openid'] = $member_info['openid_mini'];
        return $this->tokenSign($member_info);
    }


    /**
     * 绑定用户手机号码
     * @user_id 用户ID
     * @encryptedData 微信返回用户手机号码
     * @iv 微信加密算法的初始向量
     * @copyright  in highnes
     * @author: lijiao <1570693659@qq.com>
     * @return mixed
     */
    public function wxappBindPhone($data = [],$user_id = 1)
    {
        if(empty($data)) return CommonError::$userInfoNull;
        if(empty($data['encryptedData'])) return CommonError::$encryptedDataNull;
        if(empty($data['iv'])) return CommonError::$ivNull;

        $user_info = $this->modelMember->getInfo(['id'=>$user_id]);

        if(empty($user_info))
            return CommonError::$userInfoGetFail;

        //解密微信端返回的用户手机号码信息
        $code = $this->logicWxapp->decryptData($user_info['session_key'],$data['encryptedData'],$data['iv'],$data);

        if(isset($code['code']))
            return $code;

        $info = json_decode($code,true);

        if(!$this->modelMember->setInfo(['mobile'=>$info],['id'=>$user_id]))
            return CommonError::$bindPhoneFail;

        return ['data'=>$info];

    }

    /**
     * 通过code换取网页授权access_token
     * @author: chenhg <945076855@qq.com>
     * @param $code
     * @param string $ext
     * @return mixed
     */
    public function getAccessTokenByCode($code, $ext = '')
    {
        $this->wxLoginUrl = sprintf(config('wx.access_token_url'), config('wx.appid'), config('wx.appsecret'), $code);

        $result = https_request($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        $fail = array_key_exists('errcode', $wxResult);


        if ($fail) {
            return CodeBase::$failure;
        }

//        $userExist = $this->logicMember->userExistByOpenid($wxResult['openid']);
//        if ($userExist) {
//            $memberInfo = $this->modelMember->getInfo(['id' => $userExist]);
//            $wxResult['token'] = $this->tokenSign($memberInfo);
//            unset($wxResult['scope']);
//        }
//        $wxResult['user_id'] = $userExist;

        return $wxResult;

    }

}
