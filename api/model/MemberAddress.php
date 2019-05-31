<?php


namespace app\api\model;

use app\common\model\ModelBase;

class MemberAddress extends ModelBase
{
    public function getProvinceAttr($value) {
        $province['province'] = $value;
        $province['province_text'] = getCityName($value);
        return $province;
    }

    public function getCityAttr($value) {
        $city['city'] = $value;
        $city['city_text'] = getCityName($value);
        return $city;
    }

    public function getCountyAttr($value) {
        $county['county'] = $value;
        $county['county_text'] = getCityName($value);
        return $county;
    }
    
}
