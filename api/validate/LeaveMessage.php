<?php
/**
 * Created by PhpStorm.
 * copyright in highnes
 * author: lijiao <1570693659@qq.com>
 * Date: 2018/6/20 0020
 */


namespace app\api\validate;


class LeaveMessage extends ApiBase
{
    protected $rule = [
        'content'         => 'require',
        'images'          => 'max:1000000',
        'message_content' => 'require',  //留言评论验证
        'message_id' => 'require',  //留言评论验证
        'to_user_id'      => 'require', //点赞member_feedback_like
        'type'            => 'require|in:1,2,3',  //论状态（1.评论 2会员回复 3.掌柜回复）
        'entity_id'       => 'require',

    ];
    protected $message = [
        'content.require'         => '请填写你的留言',
        'comment_id.require'      => '缺少参数comment_id',
        'images.max'              => '评论图片',
        'message_content.require' => '请输入评论内容',   //留言评论验证
        'to_user_id.require'      => '缺少参数to_user_id',  //点赞comment_like
        'type.require'            => '缺少参数type',
        'entity_id.require'       => '缺少参数entity_id',

    ];
    protected $scene = [
        'add'         => ['content', 'images'],
        'leave_message_comment' => ['message_content','message_id','type'],
        'member_feedback_like'  => ['to_user_id', 'type', 'entity_id'], // 点赞类型（1.留言点赞 2.留言评论点赞）
    ];
}