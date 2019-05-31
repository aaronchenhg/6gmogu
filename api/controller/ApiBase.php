<?php
// +---------------------------------------------------------------------+
// | OneBase    | [ WE CAN DO IT JUST THINK ]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | Bigotry <3162875@qq.com>                               |
// +---------------------------------------------------------------------+
// | Repository | https://gitee.com/Bigotry/OneBase                      |
// +---------------------------------------------------------------------+

namespace app\api\controller;

use app\api\error\Common;
use app\api\logic\Token;
use app\common\controller\ControllerBase;
use think\Hook;
use think\Session;
use think\Request;

/**
 * 接口基类控制器
 */
class ApiBase extends ControllerBase
{
    public $user_id = 0;
    public $recommand_num = 10;
    public $from_type = 1;
    public $debug = true;

    /**
     * 基类初始化
     */
    public function __construct()
    {
        parent::__construct();

        $this->logicApiBase->checkParam($this->param);

        // 接口控制器钩子
        Hook::listen('hook_controller_api_base', $this->request);

        debug('api_begin');

        // 1 获取token
        $token = Request::instance()->header('token');

        // 2 根据token用户信息
        $not_login = ['wxappLogin', 'wxapp', 'h5login', 'callback', 'firstApp', 'startadv', 'getHomeWaitPayOrder', 'category', 'area', 'sendMobileCode', 'sendEmailCode', 'login'];

        if (in_array(Request::instance()->action(), $not_login)) {
            return true;
        }


        //移动端 直接授权登陆
        if (isMobile()) {
            if (!$this->debug) {
                $this->user_id = Token::getCurrentUid();
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram') !== false) {
                $this->from_type = 2;
            } else {
                $this->from_type = 1;
            }
        } else { # PC端

            if (!$this->debug) {
                $controller     = strtolower("Lvyou.LineConsult,Lvyou.LvyouLine,Lvyou.Login,Lvyou.Article,Lvyou.Index,Lvyou.Common,Lvyou.Notify,Lvyou.Search,Lvyou.Guide,Lvyou.Cooperation");
                $controller_arr = explode(',', $controller);
                $cur_control    = strtolower(Request::instance()->controller());
//                halt($cur_control);
                if (!in_array($cur_control, $controller_arr)) {
                    $this->user_id = Token::getCurrentUid();
                }

            } else {
                $this->user_id = 1;
            }
        }


    }

    /**
     * API返回数据
     */
    public function apiReturn($code_data = [], $return_data = [], $return_type = 'json')
    {

        $result = $this->logicApiBase->apiReturn($code_data, $return_data, $return_type);

        debug('api_end');

        write_exe_log('api_begin', 'api_end', DATA_NORMAL);

        return $result;
    }
}
