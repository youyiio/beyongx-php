<?php
namespace app\common\library;

class ResultCode {
	const ACTION_SUCCESS = 1;  //成功
	const ACTION_FAILED	= 0;  //失败

	/**通用错误**/
	const E_UNKNOW_ERROR = 2;  //未知错误
	const E_PARAM_ERROR	= 3;  //参数错误
	const E_PARAM_EMPTY	= 4;  //参数为空
	const E_PARAM_VALIDATE_ERROR = 5;  //参数验证错误
	const E_TOKEN_EXPIRED	= 6;  //TOKEN不合法
	const E_TOKEN_EMPTY	= 7;  //TOKEN参数缺失
	const E_TOKEN_INVALID	= 8;  //TOKEN不合法
	const E_ACCESS_NOT_AUTH	= 9;  //访问资源未授权
	const E_ACCESS_NOT_FOUND = 10;  //访问资源未找到
	const E_ACCESS_TIMEOUT  = 11;  //访问资源超时
	const E_ACCESS_LIMIT = 12;  //访问受限
	const E_DATA_NOT_FOUND = 13;  //数据为找到
	const E_DATA_EXIST = 14;  //数据已存在
	const E_DATA_ERROR = 15;  //数据错误
	const E_DATA_VALIDATE_ERROR = 16;  //数据验证错误
	const E_THIRDPARTY_ERROR = 17;  //第三方系统错误
	const E_PARSE_JSON = 18;  //json解析错误
	const E_PARSE_DATE = 19;  //date日期解析错误
	const E_LOGIC_ERROR = 20;  //逻辑层操作错误
	const E_MODEL_ERROR = 21;  //模型层操作错误
	const E_DB_ERROR = 22;  //数据库操作错误
	const E_CODE_INCORRECT = 23;  //验证码不正确
	const E_CODE_EXPIRED = 24;  //验证码已过期
	const E_CODE_USED = 25;  //验证码已使用
    

	/** Http协议异常(保留与http状态码一致: 0-999) **/
	const SC_BAD_REQUEST             = 400;       //错误请求，请检查请求地址及参数
	const SC_UNAUTHORIZED            = 401;       //未通过身份验证(用户名或密码错误)
	const SC_FORBIDDEN               = 403;       //服务器拒绝请求或非法访问
	const SC_NOT_FOUND               = 404;       //请求资源未找到，请检查请求地址及参数
	const SC_REQUEST_TIMEOUT         = 408;       //请求超时，请稍候重试
	const SC_INTERNAL_SERVER_ERROR   = 500;       //服务器内部错误，无法完成请求，请稍候重试
	const SC_BAD_GATEWAY             = 502;       //服务器宕机或正在升级
	const SC_SERVICE_UNAVAILABLE     = 503;       //服务暂不可用，服务器过载或停机维护，请稍候重试

	/** 自定义异常类型代码 **/
	/** 错误码格式为X001,X为模块编号；**/
	/** 错误标识格式E_XXX_xxxxx,其中XXX为模块标识 **/
    //用户模块
	const E_USER_NOT_EXIST               = 1001;     //用户不存在
	const E_USER_MOBILE_NOT_EXIST        = 1002;     //手机号不存在
    const E_USER_EMAIL_NOT_EXIST         = 1003;     //邮箱不存在
	const E_USER_PASSWORD_INCORRECT      = 1004;     //密码不正确
    const E_USER_MOBILE_HAS_EXIST        = 1005;     //手机号已经存在
	const E_USER_EMAIL_HAS_EXIST         = 10060;    //邮箱已经存在
	const E_USER_ACCOUNT_HAS_EXIST       = 10061;    //帐号已经存在
	const E_USER_STATE_NOT_ACTIVED       = 1006;     //用户未激活
	const E_USER_STATE_FREED             = 1007;     //用户已冻结
	const E_USER_STATE_DELETED           = 1008;     //用户已删除

    //角色权限模块
    const E_AUTH_MENU_NOT_FOUND      = 2001;   //权限菜单不存在

    //内容管理模块
    const E_CMS_ARTICLE_NOT_FOUND     = 3001;  //文章未找到
	const E_CMS_CATEGORY_NOT_SUPPORT  = 3042;  //文章分类不支持




}

?>
