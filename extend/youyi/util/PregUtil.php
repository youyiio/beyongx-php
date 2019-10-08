<?php

namespace youyi\util;

class PregUtil {
	const PREG_NUMBER = '/^[0-9]*$/';
	const PREG_NUMBERIC = '/^[0-9.]*$/';
	const PREG_EMAIL = '/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/';
    const PREG_MOBILE = '/^1[34578]\d{9}$/';
    const PREG_TELEPHONE = '(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,8}';
    const PREG_URL = '/^[a-zA-Z]+://(\w+(-\w+)*)(\.(\w+(-\w+)*))*(\?\s*)?$/';
    const PREG_IP = '/(\d+)\.(\d+)\.(\d+)\.(\d+)/g';
    const PREG_HTML_TAG = '';
    const PREG_CHINESE_CHAR = '[\u4e00-\u9fa5]';
    const PREG_ID_CARD = '/^\d{15}|\d{18}$/';
    const PREG_PASSWORD = '/^[0-9a-zA-Z@#-_]{6,20}$/';

	//是否数字
	public static function isNumber($str) {
		return preg_match(PregUtil::PREG_NUMBER, $str);
	}
	
	//是否数值
	public static function isNumberic($str) {
		return preg_match(PregUtil::PREG_NUMBERIC, $str);
	}

    //是否邮件
	public static function isEmail($str) {
		return preg_match(PregUtil::PREG_EMAIL, $str);
	}

	//判断手机号
	public static function isMobile($str) {
		return preg_match(PregUtil::PREG_MOBILE, $str);
	}

	//判断电话
	public static function isTelephone($str) {
		return preg_match(PregUtil::PREG_TELEPHONE, $str);
	}

	//判断Url
	public static function isUrl($str) {
		return preg_match(PregUtil::PREG_URL, $str);
	}

	//判断IP
	public static function isIp($str) {
		return preg_match(PregUtil::PREG_IP, $str);
	}

	//判断密码
	public static function isPassword($str) {
		return preg_match(PregUtil::PREG_PASSWORD, $str);
	}
}

?>