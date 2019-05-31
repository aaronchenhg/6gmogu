<?php
/**
 * Created by PhpStorm.
 * Author: chenhg <945076855@qq.com>
 * Date: 2018/11/8 23:25
 * Copyright in Highnes
 */

namespace app\api\model;


use app\common\model\ModelBase;

class ArticleClass extends ModelBase
{
        public function article(){



            return $this->hasMany("Article",'class_id','id')->where('status',1)->field("id,title,class_id,sub_title");
        }
}