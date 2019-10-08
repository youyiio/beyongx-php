<?php

namespace youyi\util;

class ParamUtil {
	
	/**
	 *
	 *
	 * @param toURL
	 * @param params
	 * @return
	 */
	public static function generateUrl($toURL, $params) {
		$allUrl = null;
		if (null == $toURL) {
			die("toURL is null");
		}
	
		if (!$params) {
			return $toURL;
		}
		
		$paramStr = "";
		if (is_array($params)) {
			foreach ($params as $k => $v) {			
				$v = urlencode($v);
				$paramStr .= $k . "=" . $v . "&";
			}
			if (strlen($paramStr) > 0) {
				$paramStr = substr($paramStr, 0, strlen($paramStr)-1);
			}
		} else if(is_string($params)) {
			$paramStr = $params;
		}
		
		if (strripos($toURL,"?") =="") {
			$allUrl = $toURL . "?" . $paramStr;
		} else {
			$allUrl = $toURL . "&" . $paramStr;
		}
	
		return $allUrl;
	}
	
	/**
	 * trim
	 *
	 * @param value
	 * @return
	 */
	public static function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	
	public function formatQueryString($paramMap, $urlencode){
		$buff = "";
		ksort($paramMap);
		foreach ($paramMap as $k => $v) {
			if (null != $v && "null" != $v && "sign" != $k) {
				if ($urlencode) {
					$v = urlencode($v);
				}
				$buff .= $k . "=" . $v . "&";
			}
		}
	
		$reqPar = "";
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		
		return $reqPar;
	}
	
	/**
	 * @param array $params
	 * @param string $Key
	 * @return boolean|string
	 */
	public static function sign($params, $key) {
		if (!is_array($params) || empty($key)) {
			return false;
		}
	
		ksort($params);
		$unSignParaString = self::formatQueryString($params, false);
	
		$signStr = $unSignParaString . $key;
	
		return strtolower(md5($signStr));
	}
	
	public static function arrayToXml($arr) {
		$xml = "<xml>";
		foreach ($arr as $key=>$val)
		{
			if (is_numeric($val))
			{
				$xml.="<".$key.">".$val."</".$key.">";
	
			}
			else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}
	
}

?>