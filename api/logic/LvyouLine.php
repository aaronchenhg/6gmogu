<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/23 19:32
 * Copyright in Highnes
 */

namespace app\api\logic;


use app\api\error\CodeBase;
use app\common\model\LvyouFitCrowd;
use app\common\model\LvyouLineCategory;
use app\common\model\LvyouLineDatePrice;
use app\common\model\LvyouLineInsurance;
use app\common\model\LvyouLineSpec;
use app\common\model\LvyouReachCity;
use app\common\model\LvyouSearchHistory;
use app\common\model\LvyouStartCity;
use app\common\model\LvyouTheme;
use app\api\model\MemberFavorite;

class LvyouLine extends ApiBase
{

    /**
     * 获取线路列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 21:58
     * @param $param
     * @param string $order
     * @param int|mixed $pageinate
     * @return mixed
     */
    public function getLineList($param, $order = 'a.sort desc,a.id desc', $pageinate = 12)//DB_LIST_ROWS
    {
        //sort （1.价格正序 2.价格倒序 3.销量倒序）
        if(isset($param['sort_type'])){
            if($param['sort_type'] == 1){
                $order = 'price asc';
            }else if($param['sort_type'] == 2){
                $order = 'price desc';
            }else if($param['sort_type'] == 3){
                $order = 'sale desc';
            }else if($param['sort_type'] == 4){
                $order = 'a.create_time desc';
            }
        }
        //价格区间
        if(isset($param['price_min']) && isset($param['price_max'])){
            //如果最大价格为0则查询大于最小价格以上的数据
            if($param['price_max'] == 0){
                $condition['price'] = ['>=',$param['price_min']];
            }else{
                $condition['price'] = ['between',[$param['price_min'],$param['price_max']]];
            }
        }
        //天数区间
        if(isset($param['day_min']) && isset($param['day_max'])){
            //如果最大天数为0则查询大于最小天数以上的数据
            if($param['day_max'] == 0){
                $condition['days'] = ['>=',$param['day_min']];
            }else{
                $condition['days'] = ['between',[$param['day_min'],$param['day_max']]];
            }
        }
//        halt($order);

        $params                = $this->validateLvyouLine->getDataByRule($param);
        $condition['a.status'] = 1;
        $params['start_city'] && $condition['start_city'] = $params['start_city'];
        $params['reach_city'] && $condition['reach_city'] = $params['reach_city'];
        $params['reach_school'] && $condition['reach_school'] = $params['reach_school'];
        $params['keywords'] && $condition['a.title|a.sub_title'] = ['like', "%{$params['keywords']}%"];
        $params['fit_crowd'] && $condition['fit_crowd'] = $params['fit_crowd'];
        $params['theme'] && $condition['theme'] = $params['theme'];
        $params['category'] && $condition['a.category'] = $params['category'];
        $params['is_hot'] && $condition['is_hot'] = $params['is_hot'];
        $params['is_new'] && $condition['is_new'] = $params['is_new'];
        if ($params['pagesize']) {
            $pageinate = $params['pagesize'];
        }

        // :TODO 用户id  手机端记录搜索信息
        if(isMobile()){
            if ($params['keywords']) {
                LvyouSearchHistory::addSearchRecord($params['keywords'], 1, Token::getCurrentUid());
            }
        }

        $fields = "a.id,a.title,a.is_pay,a.pre_day,a.sub_title,a.image,a.tags_img,is_new,is_hot,price,market_price,tags,days,sale,comment,
         start_city.name start_city,reach_city.name reach_city ,fit_crowd.title as fit_crowd,theme.title theme,reach_school";
        $join   = [
            ['lvyou_fit_crowd fit_crowd', 'a.fit_crowd = fit_crowd.id', 'LEFT'],
            ['lvyou_line_category category', 'a.category = category.id', 'LEFT'],
            ['lvyou_theme theme', 'a.theme = theme.id', 'LEFT'],
            ['lvyou_start_city start_city', 'a.start_city = start_city.id', 'LEFT'],
            ['lvyou_reach_city reach_city', 'a.reach_city = reach_city.id', 'LEFT'],
        ];
        $this->modelLvyouLine->alias('a');
        //浏览统计记录
        insertAccessStatist(1,1,isMobile());

        return $this->modelLvyouLine->getList($condition, $fields, $order, $pageinate, $join);
    }

    /**
     * 旅游线路详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/23 22:18
     * @param $param
     * @return mixed
     */
    public function getLineDetail($param)
    {
        $params                = $this->validateLvyouLine->getDataByRule($param);
        $condition['a.status'] = 1;
        $params['id'] && $condition['a.id'] = $params['id'];
        $fields = "a.id,a.title,a.is_pay,a.pre_days,a.pre_day_type,sub_title,a.image,banner,is_new,is_hot,price,market_price,tags,days,sale,category.title category,comment,
                    start_city.name start_city,reach_city.name reach_city ,fit_crowd.title as fit_crowd,theme.title theme,
                    content,feature,plan,cost_desc,contact,pre_day,line_no,satisfaction";

        $join = [
            ['lvyou_fit_crowd fit_crowd', 'a.fit_crowd = fit_crowd.id', 'LEFT'],
            ['lvyou_line_category category', 'a.category = category.id', 'LEFT'],
            ['lvyou_theme theme', 'a.theme = theme.id', 'LEFT'],
            ['lvyou_start_city start_city', 'a.start_city = start_city.id', 'LEFT'],
            ['lvyou_reach_city reach_city', 'a.reach_city = reach_city.id', 'LEFT'],
        ];
        $this->modelLvyouLine->alias('a');
        \app\common\model\LvyouLine::where(['id' => $params['id']])->setInc('views', 1);
        $info = $this->modelLvyouLine->getInfo($condition, $fields, $join);

        if(isMobile()){
            $info['is_favorite'] = MemberFavorite::isFavorite($param['id'],Token::getCurrentUid(),10);
        }else{
            $info['is_favorite'] = 0;
        }

        return $info;
    }

    /**
     *  获取线路日期价格
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 16:10
     * @param $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getLineDateList($param)
    {
//        halt($param);
        $this->validateIDMustBePositiveInt->goCheck();
        $params              = $this->validateIDMustBePositiveInt->getDataByRule($param);
        $condition['status'] = 1;
        $params['id'] && $condition['line_id'] = $params['id'];
        $lists = LvyouLineDatePrice::where($condition)->field("date,price,number")->select();
        return $lists;
    }


    public function getLineCategory()
    {
        $condition['status'] = 1;
        $lists               = LvyouLineCategory::where($condition)->field("id,title,image")->select();

        return $lists;
    }


    /**
     * 根据关键字查询 筛选条件
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/25 16:10
     * @param $param
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getScreenList($param)
    {

        $params = $this->validateLvyouLine->getDataByRule($param);
        $key    = '';
        if (in_array($params['key'], $params)) {
            $key = $params['key'];
        }
        if(empty($params['category'])){
            $params['category'] = 1;
        }

        $_data = [];
        switch ($key) {
            case 'start_city':
                $_data = $this->getStartCity(['cate_id' => $params['category']]);
                break;
            case 'reach_city':
                $_data = $this->getReachCity(['cate_id' => $params['category']]);
                break;
            case 'fit_crowd':
                $_data = $this->getFitCrowd(['cate_id' => $params['category']]);
                break;
            case 'theme':
                $_data = $this->getThemeList(['cate_id' => $params['category']]);
                break;
            default;

                $_data[0]['title'] = '研学主题';
                $_data[0]['name']  = 'theme';
                $_data[0]['list']  = $this->getThemeList(['cate_id' => $params['category']]);

                $_data[1]['title'] = "适合人群";
                $_data[1]['name']  = "fit_crowd";
                $_data[1]['list']  = $this->getFitCrowd(['cate_id' => $params['category']]);

                $_data[2]['title'] = "到达城市";
                $_data[2]['name']  = "reach_city";
                $_data[2]['list']  = $this->getReachCity(['cate_id' => $params['category']]);

                $_data[3]['title'] = "出发城市";
                $_data[3]['name']  = "start_city";
                $_data[3]['list']  = $this->getStartCity(['cate_id' => $params['category']]);

        }

        return $_data;
    }


    /**
     * 出发城市
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getStartCity($condition = [])
    {
        $condition['status'] = 1;

        $lists  = LvyouStartCity::where($condition)->field("id,name as title,parent_id")->select();
        $_lists = list_to_tree($lists, 'id', 'parent_id');

        return $_lists;
    }

    /**
     * 根据线路id 获取线路套餐
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:44
     * @param $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getLineSpecListById($param)
    {
        $params               = $this->validateLvyouLine->getDataByRule($param);
        $condition['line_id'] = $params['line_id'];
        $condition['status']  = 1;
        return LvyouLineSpec::where($condition)->field("id,title,price")->select();
    }

    /**
     * 线路保险列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:55
     * @param $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getLineInsuranceListById($param)
    {
        $params               = $this->validateLvyouLine->getDataByRule($param);
        $line_id              = $params['line_id'];
////        $condition['line_id'] = ['in', "0,$line_id"];
//        $condition['line_id'] = $line_id;
//        $condition['status']  = 1;
//        return LvyouLineInsurance::where($condition)->field("id,is_force,title,amount")->select();

        $insurance_ids = $this->modelLvyouLine->where('id',$line_id)->value('insrance_ids');

        $condition['id'] = $insurance_ids;
        $condition['status']  = 1;
        return LvyouLineInsurance::where($condition)->field("id,is_force,title,amount")->select();

    }


    /**
     * getHistorySearch
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 19:44
     * @param $param
     * @param $uid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getHistorySearch($param = [], $uid = 0)
    {
        $condition['uid'] = $uid;
        $data             = LvyouSearchHistory::where($condition)->group('title')->field("id,title,type")->select();
        return $data;
    }

    /**
     * 删除收缩记录
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 20:14
     * @param array $param
     * @param int $uid
     * @return int
     */
    public function delHistorySearch($param = [], $uid = 0)
    {

        $condition['uid'] = $uid;
        @$param['id'] && $condition['id'] = $param['id'];

        if($param['id'] == 0){
            $data = LvyouSearchHistory::where(['uid' => $uid])->delete();
        }else{
            $data = LvyouSearchHistory::where($condition)->delete();
        }

        if(!$data){
            return CodeBase::$failure;
        }
        return CodeBase::$success;
    }


    /**
     * 到达城市
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:55
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getReachCity($condition = [])
    {
        $condition['status'] = 1;
        $lists               = LvyouReachCity::where($condition)->order('sort asc')->field("id,name as title,parent_id")->select();
        $_lists              = list_to_tree($lists, 'id', 'parent_id');
        return $_lists;
    }

    /**
     * 适合人群
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:56
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getFitCrowd($condition = [])
    {
        $condition['status'] = 1;
        return LvyouFitCrowd::where($condition)->field("id, title")->select();
    }

    /**
     * 研学主题
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/30 12:56
     * @param $condition
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getThemeList($condition = [])
    {
        $condition['status'] = 1;
        return LvyouTheme::where($condition)->field("id,title")->select();
    }


}