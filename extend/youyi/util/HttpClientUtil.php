<?php

namespace youyi\util;

class HttpClientUtil {
	/**
	 * GET 请求
	 * @param string $url
	 */
	public function doGet($url, $params = '', $encode='') {
		if (!$url) {
			return;
		}
		if (!$encode) {
			$encode = "UTF-8";
		}
		
		$oCurl = curl_init();
		if (stripos($url,"https://") !== FALSE) {
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		
		$toUrl = ParamUtil::generateUrl($url, $params);	 
		curl_setopt($oCurl, CURLOPT_URL, $toUrl);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		$header = array(
			"content-type: application/x-www-form-urlencoded; charset=$encode"
		);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
		
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		
		if (intval($aStatus["http_code"]) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}

	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	public static function doPost($url, $params, $encode='', $post_file=false) {
		if (!$url) {
			return;
		}
		if (!$encode) {
			$encode = "UTF-8";
		}
		
		$oCurl = curl_init();
		if (stripos($url,"https://") !== false) {
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		
		if (is_string($params) || $post_file) {
			$strPOST = $params;
		} else {
			$aPOST = array();
			foreach($params as $key=>$val) {
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST = join("&", $aPOST);
		}
		
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POST, true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
		
		$header = array(
		    "content-type: application/x-www-form-urlencoded; charset=$encode"
		);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
		
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		
		if (intval($aStatus["http_code"])==200) {
			return $sContent;
		} else {
			return false;
		}
	}
}

?>