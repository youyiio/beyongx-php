<?php

namespace youyi\util;

class StringUtil {
	/**
	 * 生成随机字符串数,数字和小写字母组合
	 *
	 */
	public static function getRandString($length = 32) {
		$str = '1234567890abcdefghijklmnopqrstuvwxyz';
	
		$myRand = "";
		for ($i=0; $i < $length; $i++) {
			$j = rand(0, 35);
			$myRand .= $str[$j];
		}
	
		return $myRand;
	}
	
	/**
	 * 生成随机数字
	 *
	 */
	public static function getRandNum($length = 32) {
		$str = '1234567890';
	
		$myRand = "";
		for ($i=0; $i < $length; $i++) {
			$j = rand(0, 9);
			$myRand .= $str[$j];
		}
	
		return $myRand;
	}
}

?>