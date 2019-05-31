<?php

namespace app\api\model;


class MemberLeaveMessage extends ApiBase
{
    public function getImagesAttr($value)
    {
        $img_array = [];
        //不为空时
        if (!empty($value)) {
            $imags = unserialize($value);
            foreach ($imags as $k => $img) {

                $img_array[] = $this->prefixImgUrl($img,[]);
            }
        } else {
            $img_array = [];
        }
        return $img_array;
    }

    public function getContentAttr($value)
    {
        return strip_tags(($value));
    }

}