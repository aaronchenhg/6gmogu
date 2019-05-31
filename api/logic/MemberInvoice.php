<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 17:51
 * Copyright in Highnes
 */


namespace app\api\logic;

use app\api\error\CodeBase;
use app\common\model\MemberInvoice as MemberInvoiceModel;
use app\lib\exception\ForbiddenException;
use think\Db;

class MemberInvoice extends ApiBase
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
    public function getInvoiceList($user_id, $param = [])
    {


        $where['user_id']        = $user_id;
        $where[DATA_STATUS_NAME] = 1;
        $field                   = ['id,invoice_type,invoice_rise,invoice_title,invoice_no,email,other,status,create_time'];

        return $this->modelMemberInvoice->getList($where, $field, '', DB_LIST_ROWS);
    }

    /**
     * 获取会员地址详细
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:17
     * @param $id
     * @return mixed
     */
    public function getInvoiceDetail($param)
    {
        $this->validateMemberInvoice->goCheck('del');

        $where['id'] = $param['id'];
        $field       = 'id,invoice_type,invoice_rise,invoice_title,invoice_no,email,other,status,create_time';
        $info        = $this->modelMemberInvoice->getInfo($where, $field);


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
    public function addInvoice($user_id, $param)
    {
        $this->validateMemberInvoice->goCheck('add');
        $data = $this->validateMemberInvoice->getDataByRule($param);

        $data['uniacid'] = 1;
        $data['status'] = 1;
        $data['user_id'] = $user_id;
        $data['create_time'] = time();

        MemberInvoiceModel::startTrans();
        try {
            $res = MemberInvoiceModel::create($data);
            MemberInvoiceModel::commit();
        } catch (Exception $exception) {

            MemberInvoiceModel::rollback();
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
    public function editInvoice($param)
    {
        $this->validateMemberInvoice->goCheck('edit');
        $data = $this->validateMemberInvoice->getDataByRule($param);

        $data = array_filter($data);

        $data['update_time'] = time();
        MemberInvoiceModel::startTrans();
        try {
            $res = MemberInvoiceModel::update($data);
            MemberInvoiceModel::commit();
        } catch (Exception $exception) {

            MemberInvoiceModel::rollback();
            throw new ForbiddenException([
                'msg' => $exception->getMessage()
            ]);
        }
        return $res;
    }

    /**
     * 删除地址（改变状态假删除）
     * @copyright  in highnes
     * @author: chenhg <945076855@qq.com>
     * @date: 2018/10/28 2:16
     * @param $param
     * @return array
     */
    public function delInvoice($param)
    {
        $this->validateMemberInvoice->goCheck('del');

        $data = $this->validateMemberAddress->getDataByRule($param);

        $data           = array_filter($data);
        $data['status'] = -1;
        $res            = $this->modelMemberInvoice->setInfo($data);
        if ($res) {
            return CodeBase::$success;
        } else {
            return CodeBase::$failure;
        }
    }


}