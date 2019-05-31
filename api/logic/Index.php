<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/5 17:10
 * Copyright in Highnes
 */

namespace app\api\logic;


use app\api\model\Notice;
use app\common\model\Banner;
use app\common\model\LvyouSearchHistory;

class Index extends ApiBase
{


    /**
     * 公告列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 17:12
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getNoticeList()
    {
        $condition['status'] = 1;
        $field               = "id,title,create_time";
        $data                = Notice::where($condition)->field($field)->select();
        return $data;
    }

    /**
     * 公告详情
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 17:12
     */
    public function getNoticeDetail($param)
    {
        $field               = "id,title,content,create_time";
        $condition['status'] = 1;
        @$param['id'] && $condition['id'] = @$param['id'];
        $data = Notice::where($condition)->field($field)->find();
        return $data;
    }

    /**
     * banner列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/5 17:16
     */
    public function getBannerList()
    {

        //判断显示位置 1为手机
        if(isMobile() == 1){
            $condition['show_position'] = 1;
        }else{
            $condition['show_position'] = 2;
        }

        $field = "title,id,create_time,is_advertisement,link,thumb";
        $condition['status'] = 1;
        $condition['is_advertisement'] = 0;
        $lists = Banner::where($condition)->field($field)->select();
        return $lists;
    }



}