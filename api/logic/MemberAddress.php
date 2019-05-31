<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 17:51
 * Copyright in Highnes
 */


namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\model\MemberAddress as MemberAddressModel;
use app\lib\exception\ForbiddenException;
use think\Db;

class MemberAddress extends ApiBase
{


    /**
     * 获取会员地址列表
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:18
     * @param $user_id
     * @param $param
     * @return mixed
     */
    public function getAddressList($user_id, $param = [])
    {


        $where['user_id']        = $user_id;
        $where[DATA_STATUS_NAME] = 1;
        $field                   = 'id,username,mobile,province,city,county,address,is_default';

        return $this->modelMemberAddress->getList($where, $field, '', DB_LIST_ROWS);
    }

    /**
     * 获取会员地址详细
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:17
     * @param $id
     * @return mixed
     */
    public function getAddressDetail($param)
    {
        $this->validateMemberAddress->goCheck('del');

        $where['id'] = $param['id'];
        $field       = 'id,username,mobile,province,city,county,address,is_default';
        $info        = $this->modelMemberAddress->getInfo($where, $field);

//        $info['address'] = getCityName($info['province']['province']).getCityName($info['city']['city']).getCityName($info['county']['county']).$info['address'];

        return $info;

    }

    /**
     * 添加会员地址
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:17
     * @param $user_id
     * @param $param
     * @return $this
     * @throws ForbiddenException
     */
    public function addAddress($user_id, $param)
    {
        $this->validateMemberAddress->goCheck('add');
        $data = $this->validateMemberAddress->getDataByRule($param);

        $where  = [
            'user_id' => $user_id,
            'status'  => 1,
        ];
        $isNull = $this->modelMemberAddress->where($where)->field('id')->find();
        //如果数据库没有地址则添加第一条为默认地址
        empty($isNull) ? $data['is_default'] = 1 : $data['is_default'] = 2;

        $data['uniacid'] = 1;
        $data['user_id'] = $user_id;

        MemberAddressModel::startTrans();
        try {
            $res = MemberAddressModel::create($data);
            MemberAddressModel::commit();
        } catch (Exception $exception) {

            MemberAddressModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 编辑会员地址
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:17
     * @param $param
     * @return $this
     * @throws ForbiddenException
     */
    public function editAddress($param)
    {
        $this->validateMemberAddress->goCheck('edit');
        $data = $this->validateMemberAddress->getDataByRule($param);

        $data = array_filter($data);

        $data['update_time'] = time();
        MemberAddressModel::startTrans();
        try {
            $res = MemberAddressModel::update($data);
            MemberAddressModel::commit();
        } catch (Exception $exception) {

            MemberAddressModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 设置会员默认地址
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:17
     * @param $user_id
     * @param $param
     * @return array
     * @throws ForbiddenException
     */
    public function setDefaultAddress($user_id, $param)
    {
        $this->validateMemberAddress->goCheck('del');
        $data                = $this->validateMemberAddress->getDataByRule($param);
        $data = array_filter($data);
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
            throw new ForbiddenException([
                'msg' => $e->getMessage()
            ]);
        }
        return CodeBase::$success;
    }

    /**
     * 删除地址（改变状态假删除）
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:16
     * @param $param
     * @return array
     */
    public function delAddress($param)
    {
        $this->validateMemberAddress->goCheck('del');

        $data           = $this->validateMemberAddress->getDataByRule($param);
        $data['status'] = -1;
        $res            = $this->modelMemberAddress->setInfo($data);
        if ($res) {
            return CodeBase::$success;
        } else {
            return CodeBase::$failure;
        }
    }


}