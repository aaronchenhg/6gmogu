<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/31 14:33
 * Copyright in Highnes
 */

namespace app\api\logic;

use app\api\error\CodeBase;
use \app\common\model\LvyouOrder as LvyouOrderModel;
use app\common\model\LvyouOrderComment;
use app\common\model\LvyouOrderContact;
use app\common\model\LvyouOrderDetail;
use app\common\model\LvyouOrderLog;
use app\lib\exception\ForbiddenException;
use app\lib\exception\OrderException;
use think\Db;
use think\Exception;

/**
 * 订单状态订单状态（-1：删除，1：待支付，2：自动关闭；3：已取消；10：已支付；11:申请退款,12：已退款；13：已使用；14：已完成，15：已评价）
 * @author: chenhg <945076855@qq.com>
 * Copyright in Highnes
 * @package app\api\logic
 */
class LvyouOrder extends ApiBase
{


    /**
     * 订单列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/31 14:44
     * @param $uid 用户ID
     * @param $param
     * @param int|mixed $pageinate
     * @return mixed
     */
    public function getOrderList($uid, $param = [], $pageinate = DB_LIST_ROWS)
    {
        $condition = [];
        if (array_key_exists('status', $param) && !empty($param['status'])) {
            $condition['order_status'] = $param['status'];
        } else {
            $condition['order_status'] = ['neq', -1];
        }
        $condition['uid'] = $uid;
        $fields           = "id,uid,line_id,insurance_id,spec_id,total_amount,insurance_amount,promot_amount,line_amount,play_date,
                                       people_number,is_support_refund,child_number,create_time,pay_time,order_status,mobile,order_sn,email";
        $order            = "id desc";

        $data = LvyouOrderModel::where($condition)->with(['orderDetail'])->field($fields)->order($order)->paginate($pageinate);

        //浏览日志
        insertAccessStatist(1,3,isMobile());

        return $data;
    }

    /**
     * getOrderDetail
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: xxx
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOrderDetail($param = [])
    {

        $condition['order_status'] = ['neq', -1];
        $params                    = $this->validateLvyouOrder->getDataByRule($param);
        $condition['id']           = $params['id'];
        $fields                    = "id,uid,line_id,insurance_id,ewm,spec_id,total_amount,insurance_amount,promot_amount,line_amount,play_date,
                                       people_number,is_support_refund,child_number,create_time,pay_time,order_status,mobile,order_sn,email,remark";
        $order                     = "id desc";
        $data                      = LvyouOrderModel::where($condition)->with(['orderDetail', 'orderLog', 'orderContact', 'orderInsurance'])->field($fields)->order($order)->find();
        if(!empty($data)){
            $data['pay_money'] = $this->modelConfig->where('name','pay_money')->value('value');
            $data['pay_method'] = $this->modelConfig->where('name','pay_method')->value('value');
        }
        return $data;
    }


    /**
     * 取消订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 20:49
     * @param $param param['id']
     * @param $uid  用户ID
     * @return $this
     * @throws ForbiddenException
     * @throws OrderException
     */
    public function cancelOrder($param, $uid)
    {
        $this->validateLvyouOrder->goCheck();
        $params          = $this->validateLvyouOrder->getDataByRule($param);
        $condition['id'] = $params['id'];
        // 判断订单是否可以取消
        $orderid = LvyouOrderModel::where($condition)->where('order_status', 'lt', 10)
            ->where('order_status', 'neq', 3)->value('id');
        if (empty($orderid)) {
            throw  new  OrderException(['msg' => "订单不能取消"]);
        }

        Db::startTrans();
        try {
            LvyouOrderModel::where($condition)->update(['cancel_time' => time(), 'order_status' => 3]);
            $res = LvyouOrderLog::addLog($orderid, $uid, '用户取消订单');
            Db::commit();
        } catch (Exception $exception) {
            throw new ForbiddenException(['订单取消失败,请重试']);
            Db::rollback();
        }
        return $res;
    }

    /**
     * 关闭订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 20:54
     * @param $param
     * @param $uid 用户ID
     * @return $this
     * @throws ForbiddenException
     * @throws OrderException
     */
    public function closeOrder($params, $uid,$remark = '关闭订单')
    {
//        $this->validateLvyouOrder->goCheck();
//        $params          = $this->validateLvyouOrder->getDataByRule($param);
        $condition['id'] = $params['id'];
        // 判断订单是否可以取消
        $orderid = LvyouOrderModel::where($condition)->where('order_status', 'lt', 10)
            ->where('order_status', 'neq', 2)->value('id');
        if (empty($orderid)) {
            return CodeBase::errorMessage(10000,'订单不能取消');

        }

        Db::startTrans();
        try {
            LvyouOrderModel::where($condition)->update(['close_time' => time(), 'order_status' => 2]);
            $res = LvyouOrderLog::addLog($orderid, $uid, $remark);
            Db::commit();
        } catch (Exception $exception) {
            return CodeBase::errorMessage(10000,'订单取消失败,请重试');
            Db::rollback();
        }
        return $res;
    }

    /**
     * 申请退款
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:01
     * @param $param param['id']
     * @param $uid 用户id
     * @return $this
     * @throws ForbiddenException
     * @throws OrderException
     */
    public function applyRefundOrder($param, $uid)
    {
        $this->validateLvyouOrder->goCheck();
        $params          = $this->validateLvyouOrder->getDataByRule($param);
        $condition['id'] = $params['id'];
        // 判断订单是否可以取消
        $orderid = LvyouOrderModel::where($condition)->where('order_status', 'eq', 10)
            ->where('order_status', 'neq', 11)->value('id');
        if (empty($orderid)) {
            throw  new  OrderException(['msg' => "订单不能退款"]);
        }

        Db::startTrans();
        try {
            LvyouOrderModel::where($condition)->update(['close_time' => time(), 'order_status' => 11,'update_time' => time()]);
            $res = LvyouOrderLog::addLog($orderid, $uid, '申请退款');
            Db::commit();
        } catch (Exception $exception) {
            throw new ForbiddenException(['订单取消失败,请重试']);
            Db::rollback();
        }
        return $res;
    }

    /**
     * 删除订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 21:17
     * @param $param
     * @param $uid 用户id
     * @return $this
     * @throws ForbiddenException
     * @throws OrderException
     */
    public function deleteOrder($param, $uid)
    {
        $this->validateLvyouOrder->goCheck();
        $params          = $this->validateLvyouOrder->getDataByRule($param);
        $condition['id'] = $params['id'];
        // 判断订单是否可以取消
        $orderid = LvyouOrderModel::where($condition)->where('order_status', 'in', [2, 3, 14, 15])
            ->value('id');
        if (empty($orderid)) {
            throw  new  OrderException(['msg' => "订单不能退款"]);
        }

        Db::startTrans();
        try {
            LvyouOrderModel::where($condition)->update(['close_time' => time(), 'order_status' => -1]);
            $res = LvyouOrderLog::addLog($orderid, $uid, '删除订单');
            Db::commit();
        } catch (Exception $exception) {
            throw new ForbiddenException(['订单取消失败,请重试']);
            Db::rollback();
        }
        return $res;
    }


    /**
     * 汇总订单状态数量
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 20:41
     * @param $uid 用户id
     * @return mixed
     */
    public function getOrderCountList($param, $uid)
    {
        $_data['all']          = $this->getOrderCountByStatus(0, $uid);
        $_data['wait_pay']     = $this->getOrderCountByStatus(1, $uid);
        $_data['wait_play']    = $this->getOrderCountByStatus(10, $uid);
        $_data['wait_comment'] = $this->getOrderCountByStatus(14, $uid);
        return $_data;
    }


    /**
     * 根据订单状态获取订单数量
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/6 20:37
     * @param $order_status  订单状态（-1：删除，1：待支付，2：自动关闭；3：已取消；10：已支付；11:申请退款,12：已退款；13：已使用；14：已完成，15：已评价）
     * @param $uid 用户id
     * @return int|string
     */
    public function getOrderCountByStatus($order_status, $uid)
    {
        $order_status && $condition['order_status'] = $order_status;
        $condition['uid'] = $uid;


        $count = LvyouOrderModel::where($condition)->count('id');
        return $count;
    }


    /**
     * 评价订单
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 16:42
     * @param $param
     * @param $uid
     * @return $this
     */
    public function addComment($param, $uid)
    {
        $this->validateLvyouOrderComment->goCheck();
        $params = $this->validateLvyouOrderComment->getDataByRule($param);

        $data                = array_filter($params);
        $data['create_time'] = time();
        $data['user_id']     = $uid;
        $data['status']      = 2;
        $data['line_id']     = LvyouOrderModel::where(['id' => $params['order_id']])->value('line_id');
        $data['nickname']    = \app\common\model\Member::where(['id' => $uid])->value("nickname");
        $data['headimgurl']  = \app\common\model\Member::where(['id' => $uid])->value("headimgurl");

        return LvyouOrderComment::create($data);
    }


    /**
     * 获取订单状态
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 15:51
     */
    public function getOrderStatus($param)
    {

        $condition['order_sn'] = @$param['order_sn'];
        return LvyouOrderModel::where($condition)->field("order_sn,order_status")->find();
    }

    /**
     * 评论列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/11/8 17:09
     * @param $uid 用户id
     * @param array $param
     * @param int|mixed $pageinate
     */
    public function getOrderCommentList($uid, $param = [], $pageinate = DB_LIST_ROWS)
    {
        $condition['status'] = 1;
        if (!empty($param)) {
            if (array_key_exists('line_id', $param)) {
                $condition['line_id'] = $param['line_id'];
            }
            if (array_key_exists('order_id', $param)) {
                $condition['order_id'] = $param['order_id'];
            }
        }
        return LvyouOrderComment::where($condition)->field("line_id,id,nickname,headimgurl,content,create_time,score,reply_content,is_anonymous")->paginate($pageinate);
    }


    /**
     * 批量取消订单
     */
    public  function batchCancelOrder()
    {
        //TODO 获取全局配置订单取消时间
        $where = [];
        $where['order_status'] = 1;
        $where['create_time'] = ['lt', time() - 60 * 60 * 24];
        $res = Db::name('LvyouOrder')->where($where)->field('id,uid,order_sn,create_time')->select();

        if ($res) {

            foreach ($res as $k => $v) {
                $this->closeOrder(['id'=>$v['id']], $v['uid'],'超时未付款自动关闭订单');
            }
        }
    }

}