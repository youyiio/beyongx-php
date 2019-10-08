<?php
// +----------------------------------------------------------------------
// | XiuXiu [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.xiuxiu.io All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: cattong <aronter@gmail.com>
// +----------------------------------------------------------------------

namespace youyi\util;

class Array2dUtil {
	/**
	 * 二维数组按整行值 去重
	 * @param array 2d $array2D
	 * @return array 2d 去重后的二维数组
	 */
	
	public static function array_unique_2d($array2D, $stkeep=true, $ndformat=true) {
		// 判断是否保留一级数组键 (一级数组键可以为非数字)
		if($stkeep) $stArr = array_keys($array2D);
	
		// 判断是否保留二级数组键 (所有二级数组键必须相同)
		if($ndformat) $ndArr = array_keys(end($array2D));
	
		//降维,也可以用implode,将一维数组转换为用逗号连接的字符串
		foreach ($array2D as $v){
			$v = join(",",$v);
			$temp[] = $v;
		}
	
		//去掉重复的字符串,也就是重复的一维数组
		$temp = array_unique($temp);
		$temp = array_merge($temp);//重新整理下标
		 
		//再将拆开的数组重新组装
		foreach ($temp as $k => $v)
		{
			if($stkeep) $k = $stArr[$k];
			if($ndformat)
			{
				$tempArr = explode(",",$v);
				foreach($tempArr as $ndkey => $ndval)
					$output[$k][$ndArr[$ndkey]] = $ndval;
			} else {
				$output[$k] = explode(",",$v);
			}
		}
	
		return $output;
	}
	
	/**
	 * 二维数组，按某个值去重
	 * @param array 2d $array2d
	 * @param id||string $key
     * @return array 2d 去重后的二维数组
	 */
	public static function array_unique_2d_single($array2d, $key)	{
		$tmp_arr = array();
		foreach($array2d as $k => $v)
		{
			if(in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
			{
				unset($array2d[$k]);
			}
			else {
				$tmp_arr[] = $v[$key];
			}
		}
	}

    /**
     * 二维数组，是否包含一维数组
     * @param array 1d $array2d
     * @param array 2d $array2d
     * @return bool
     */
	public static function in_array2d($array1d, $array2d) {
        if (!is_array($array1d) || !is_array($array2d)) {
        	return false;
        }

        foreach ($array2d as $k => $v) {
        	$diffs = array_diff($v, $array1d);
        	if (!$diffs) {
                return true;
        	}
        }

        return false;
	}
}

?>