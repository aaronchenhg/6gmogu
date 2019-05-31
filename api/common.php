<?php

use \Firebase\JWT\JWT;
use think\Config;



// 解密user_token
function decoded_user_token($token = '')
{
    
    $decoded = JWT::decode($token, API_KEY . JWT_KEY, array('HS256'));

    return (array) $decoded;
}

// 获取解密信息中的data
function get_member_by_token($token = '')
{
    
    $result = decoded_user_token($token);

    return $result['data'];
}

// 数据验签时数据字段过滤
function sign_field_filter($data = [])
{
    
    $data_sign_filter_field_array = config('data_sign_filter_field');
    
    foreach ($data_sign_filter_field_array as $v)
    {
        
        if (array_key_exists($v, $data)) {
            
            unset($data[$v]);
        }
    }
    
    return $data;
}

// 过滤后的数据生成数据签名
function create_sign_filter($data = [], $key = '')
{
    
    $filter_data = sign_field_filter($data);
    
    return empty($key) ? data_md5_key($filter_data, API_KEY) : data_md5_key($filter_data, $key);
}

function treatmentSpec($data)
{
    if(empty($data) || !isset($data['goods_spec']) || !isset($data['spec_info_list'])) return false;

    $spec_lists = array_map(function ($value)
    {
        if(!empty($value['value']))
        {
            $vv_spec_value = array_column($value['value'],'spec_value_id');
        }

        return $vv_spec_value;
    },$data['spec_info_list']);

    $spec = getSpecToString($spec_lists);

    $stock = collection($data['goods_spec'])->toArray();
    $stock_lists = array_column($stock,'specs');

    foreach ($spec as $spec_key=>$spec_value)
    {
        $spec_stock = 0;
        foreach ($stock as $k_stock=>$v_stock)
        {
            $v_stock['specs'] = explode('_',$v_stock['specs']);

            //$spec_value在数组$v_stock中
            if(arrayInArray($spec_value,$v_stock))
            {
                $spec_stock += $v_stock['stock'];
            }
        }
        if(is_array($spec[$spec_key]))
        {
            $spec[$spec_key]['stock'] = $spec_stock;
        }else
        {
            $values[0] = $spec[$spec_key];
            $values['stock'] = $spec_stock;
            array_push($spec,$values);
            unset($spec[$spec_key]);
        }
//
    }
    return $spec;
}
if(!function_exists('arrayInArray'))
{
    function arrayInArray($array1,$array2)
    {
        $flag = 0;
        if(is_array($array1))
        {
            foreach ($array1 as $va) {
                if (in_array($va, $array2['specs'])) {
                    continue;
                }else {
                    return false;
                }
            }
        }else
        {
            if (!in_array($array1, $array2['specs'])) {
                return false;
            }
        }

        return true;
    }
}

function sortArray($data)
{
   if(!is_array($data)) return false;

    $count = 1;//组合个数统计
    $start_len = count($data[0]);

    $start_test = $data[0][0];

    $len_data = count($data);

    print_r(getArrayBySpec($data['spec_info_list']));exit;
    while(true)
    {
        array_map(function ($value)
        {
            for ($i = 0;$i < count($value);$i++)
            {
                $j = $i++; //$j = 0;$i = 1;

                $start_test_value = '_'.$value[$i];
            }
        },$data);
    }
    exit;
}

function getSpecToString($data = [])
{
    if(!is_array($data)) return false;

    $count = count($data);

    $info = combineDika($data);

    $first = array_shift($data);

    for ($i = 0;$i<$count-1;$i++)
    {
        $lists = array();
        $lists[] = $first;
        $lists[] = $data[$i];
        $arr = combineDika($lists);
//        print_r($data[$i]);
        $info = array_merge($info,$arr);

        for ($j = 0;$j<count($data[$i]);$j++)
        {
            $info[] = $data[$i][$j];
        }
    }
    for ($j = 0;$j<count($first);$j++)
    {
        $info[] = $first[$j];
    }
    return $info;
}
/**
 * 所有数组的笛卡尔积
 *
 * @param unknown_type $data
 */
function combineDika($data) {
//    $data = func_get_args();
    $cnt = count($data);

    $result = array();
    foreach($data[0] as $item) {
        $result[] = array($item);
    }
    for($i = 1; $i < $cnt; $i++) {
        $result = combineArray($result,$data[$i]);
    }
    return $result;
}
/**
 * 两个数组的笛卡尔积
 *
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1,$arr2) {
    $result = array();
    foreach ($arr1 as $item1) {
        foreach ($arr2 as $item2) {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}

/**
 * 数组求和
 */
if(!function_exists('getSum'))
{
    function getSum($data)
    {
        return array_sum($data);
    }
}

if(!function_exists('getImageUrl'))
{
    function getImageUrl($data)
    {
        if(is_array($data))
        {
            foreach ($data as $k=>$value)
            {
                $data[$k] =  Config::get('http_name').'/'.$value;
            }
        }else
        {
            $data = Config::get('http_name').'/'.$data;
        }
        return $data;
    }
}
