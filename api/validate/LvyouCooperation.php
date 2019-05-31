<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/10/25 16:16
 * Copyright in Highnes
 */

namespace app\api\validate;


class LvyouCooperation extends ApiBase
{
    protected $rule = [
        //产品合作
        'product_name'          => 'require',           // 产品名称
        'product_type'          => 'require',           // 产品类型
        'core'                  => 'require',           // 核心素养
        'product_content'       => 'require',           // 产品内容

        //基地合作
        'base_name'             => 'require',           // 基地名称
        'base_address'          => 'require',           // 基地地址
        'base_img'              => 'require',           // 基地图片
        'base_license_img'      => 'require',           // 附件(营业执照)

        //机构合作
        'organization_name'         => 'require',           // 机构名称
        'theme'                     => 'require',           // 机构主题
        'content'                   => 'require',           // 机构内容
        'organization_license_img'  => 'require',           // 附件(营业执照)


        //通用
        'user_name'             => 'require',            // 姓名
        'mobile'                => 'require|isMobile',           // 电话
        'email'                 => 'require|email',              // 邮箱

    ];

    protected $message = [
        //产品合作
        'product_name.require'           => '请输入产品名称',
        'product_type.require'           => '请输入产品类型',
        'core.require'                   => '请输入核心素养',
        'product_content.require'        => '请输入产品内容',


        //基地合作
        'base_name.require'              => '请输入基地名称',
        'base_address.require'           => '请输入基地地址',
        'base_img.require'               => '请上传基地图片',
        'base_license_img.require'       => '请上传营业执照',

        //机构合作
        'organization_name.require'           => '请输入机构名称',
        'theme.require'                       => '请输入机构主题',
        'content.require'                     => '请输入机构内容',
        'organization_license_img.require'    => '请上传营业执照',


        //通用
        'user_name.require'              => '请输入姓名',
        'mobile.require'                 => '请输入联系方式',
        'mobile.isMobile'                => '联系方式不正确',
        'email.require'                  => '请输入邮箱',
        'email.email'                    => '邮箱不正确',
    ];

    protected $scene = [
        //产品合作
        'productConnection'         => ['product_name','product_type','core','product_content','user_name','mobile','email'],
        //基地合作
        'baseConnection'            => ['base_name','base_name','base_img','base_license_img','user_name','mobile','email'],
        //机构合作
        'organizationConnection'    => ['organization_name','theme','content','organization_license_img','mobile'],
        //代理人合作
        'agentConnection'           => ['user_name','mobile'],

    ];

}