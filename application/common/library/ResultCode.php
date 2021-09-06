<?php
namespace app\common\library;

class ResultCode {
    const ACTION_SUCCESS = 1; //操作成功
	const ACTION_FAILED = 0; //操作失败

	/**参数**/
	const E_UNKNOW_ERROR = 2;  //未知异常
	const E_PARAM_ERROR = 3;   //参数错误
	const E_PARAM_NULL = 4;    //参数为空
	const E_DATA_NOT_FOUND = 5;  //数据为空
	const E_DATA_VERIFY_ERROR = 6;  //数据验证错误，【不加入result_code说明】

	/** Http协议异常(保留与http状态码一致: 0-999) **/
	const SC_BAD_REQUEST             = 400;       //错误请求，请检查请求地址及参数
	const SC_UNAUTHORIZED            = 401;       //未通过身份验证(用户名或密码错误)
	const SC_FORBIDDEN               = 403;       //服务器拒绝请求或非法访问
	const SC_NOT_FOUND               = 404;       //请求资源未找到，请检查请求地址及参数
	const SC_REQUEST_TIMEOUT         = 408;       //请求超时，请稍候重试
	const SC_INTERNAL_SERVER_ERROR   = 500;       //服务器内部错误，无法完成请求，请稍候重试
	const SC_BAD_GATEWAY             = 502;       //服务器宕机或正在升级
	const SC_SERVICE_UNAVAILABLE     = 503;       //服务暂不可用，服务器过载或停机维护，请稍候重试

	/**（保留号: 1000-1999)**/
	// 自定义异常类型代码
	const API_UNSUPPORTED            = 1000;     //API暂时没有支持
	const API_VERSION_TOO_LOW        = 1001;     //您使用的版本过低，建议升级
	const API_AUTH_ERROR             = 1002;     //用户登陆授权失败
	const API_REQUEST_TIME_OUT       = 1003;     //请求时间超时
    const ACCESS_NOT_AUTH            = 1010;     //访问未授权
    const ACCESS_ID_NOT_EXIST        = 1011;     //acesss id不存在
	const FILE_NOT_FOUND             = 1050;     //文件不存在
	const FILE_TYPE_INVALID          = 1051;     //文件类型不正确

	// 网络问题异常代码
	const NET_ISSUE                      = 1100;      //网络出现异常
	const NET_SOCKET_TIME_OUT            = 1101;      //网络超时,请确认网络是否稳定
	const NET_I_O_EXCEPTION              = 1102;      //网络出现异常,请确认网络是否稳定
	const NET_UNCONNECTED                = 1103;      //网络未连接
	const NET_CLIENT_PROTOCOL_EXCEPTION  = 1110;      //客户端协议异常
	const NET_HTTPS_UNDER_CMWAP          = 1111;      //wap下无法使用https,建议切换成net网络

	const URL_MALFORMED_ERROR            = 2010;      //URL地址格式不正确
	const URI_SYNTAX_ERROR               = 2011;      //URI地址语法错误
	const JSON_PARSE_ERROR               = 2020;      //JSON解析错误
	const DATE_PARSE_ERROR               = 2030;      //日期解析错误
	const THIRDPARTY_SYSTEM_ERROR        = 2050;      //第三方系统交互异常

	const SIGN_NOT_EXIST                 = 3001;      //参数未进行签名
	const SIGN_VERIFY_ERROR              = 3002;      //签名验证错误，可能版本过低，建议升级
	const FORMAT_MOBILE_INCORRECT        = 3003;      //手机号格式不正确
	const FORMAT_PASSWORD_INCORRECT      = 3004;      //密码格式不正确
	const PARAMS_VALIDATE_INVALID        = 3005;      //参数验证不正确

    const E_DB_OPERATION_ERROR           = 4001;      //数据库操作错误
    const E_MODEL_OPERATION_ERROR        = 4011;      //模型层操作错误
    const E_MODEL_DATA_VERIFY_ERROR      = 4012;      //模型层数据验证错误
    const E_LOGIC_OPERATION_ERROR        = 4021;      //业务逻辑层操作错误
    const E_LOGIC_DATA_VERIFY_ERROR      = 4022;      //业务逻辑层数据验证错误

	const E_USER_NOT_EXIST               = 10001;     //用户不存在
	const E_USER_MOBILE_NOT_EXIST        = 10002;     //手机号不存在
    const E_USER_EMAIL_NOT_EXIST         = 10003;     //邮箱不存在
	const E_USER_PASSWORD_INCORRECT      = 10004;     //密码不正确
    const E_USER_MOBILE_HAS_EXIST        = 10005;     //手机号已经存在
	const E_USER_EMAIL_HAS_EXIST         = 100060;    //邮箱已经存在
	const E_USER_ACCOUNT_HAS_EXIST       = 100061;    //帐号已经存在
	const E_USER_STATE_NOT_ACTIVED       = 10006;     //用户未激活
	const E_USER_STATE_FREED             = 10007;     //用户已冻结
	const E_USER_STATE_DELETED           = 10008;     //用户已删除

	const E_USER_VERIFY_CODE_CODE_INCORRECT = 10011;  //验证码不正确
	const E_USER_VERIFY_CODE_CODE_USED      = 10012;  //验证码已使用
    const E_USER_VERIFY_CODE_CODE_EXPIRED   = 10013;  //验证码已过期

    const E_USER_TOKEN_INFO_TOKEN_INVALID    = 10021;  //授权token不合法
    const E_USER_TOKEN_INFO_STATUS_DISABLED  = 10022;  //授权token失效
    const E_USER_TOKEN_INFO_STATUS_EXPIRED   = 10023;  //授权token已过期

    const E_DATA_STATUS_INVALID		= 10031; 	//数据状态不合法

    const E_PAY_CHANNEL_NOT_SUPPORT 	= 10041;  //支付渠道不支持
	const E_PAY_TYPE_NOT_SUPPORT 	 = 10042;  //支付类型不支持
    const E_PAY_SCANCOLLECT_FAIL    = 10051;  //扫码收款失败
    const E_PAY_REFUNDORDER_FAIL    = 10052;  //退款失败
	const E_ORDER_ID_NOT_EXIST      = 10060; //订单id不存在

    const E_MERCHANT_STATUS_ERROR   = 10070; //门店状态错误
	const E_WALLET_BALANCE_NOT_ENOUGH = 10080; //余额不足
	const E_WALLET_POINTS_NOT_ENOUGH = 10081; //积分不足

}

?>
