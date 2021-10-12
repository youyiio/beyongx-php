<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\facade\Config;
use think\facade\Env;
use think\facade\Log;

//cms核心表前缀;
define('CMS_PREFIX', 'cms_');
if (\think\facade\Config::get('cache.type') == 'Redis')
    define('CACHE_SEPARATOR', ':');
else
    define('CACHE_SEPARATOR', '_');

function ip()
{
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
    return $res;
    //dump(phpinfo());//所有PHP配置信息
}

//客户端信息解析成数组,字段与服务器对应
function get_client_info()
{
    $client = htmlspecialchars_decode(input('client'));
    $client = json_decode($client, true);

    if (empty($client) || !is_array($client)) {
        return $client;
    }

    $arr = [];
    foreach ($client as $k => $v) {
        $key = parse_name($k);
        $arr[$key] = $v;
    }
    return $arr;
}

/**
 * ip转化为地址
 * @param $ip  纯数字或ipv4字符串
 * @param string $fields 指定拼接的字段【country, province, city, isp】
 * @return string
 */
function ip_to_address($ip, $fields = '')
{
    if (empty($ip)) {
        return '';
    }

    include_once Env::get('root_path') . 'extend/' . 'ipipnet/IP4datx.class.php';

    if (is_numeric($ip) || count(explode('.', $ip)) == 0) {
        $ip = long2ip($ip);
    }

    //添加地区
    $country = '未知';
    $province = '未知';
    $city = '未知';
    $isp = '';
    try {
        $ipInfo = \IP::find($ip);
        $country = $ipInfo[0];
        $province = $ipInfo[1];
        $city = $ipInfo[2];
        $isp = $ipInfo[3];
    } catch (Exception $e) {
        Log::error($e);
    }
    $data['country'] = $country;
    $data['province'] = $province;
    $data['city'] = $city;
    $data['isp'] = $isp;

    //移除不需要的字段
    if (!empty($fields)) {
        $values = '';
        $fields = explode(',', $fields);
        foreach ($data as $k => $v) {
            if (in_array($k, $fields)) {
                $values .= $v;
            }
        }
        return $values;
    }

    return $data;
}

//获取当前完整的url路径
function get_cur_url()
{
//    $url = 'http://';
//    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
//        $url = 'https://';
//    }
//
//    if ($_SERVER['SERVER_PORT'] != '80') {
//        $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
//    } else {
//        $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
//    }
//
//    return $url;

    $request = request();
    if ($request->isCli()) {
        $scheme = $request->header('scheme') ? $request->header('scheme') : $request->scheme();
        return $scheme . '://' . $request->header('x-original-host') . $request->server('REQUEST_URI');
    } else {//cgi
        return $request->url(true);
    }
}

/**
 * 压缩文件|文件夹
 * @param string $path 需要压缩的文件[夹]路径
 * @param string $zipFile 压缩文件所保存的目录
 * @return string zip文件路径
 */
function x_zip($path, $zipFile)
{
    set_time_limit(0);

    $zip = new \ZipArchive();
    $zip->open($zipFile, ZIPARCHIVE::CREATE);

    $path = preg_replace('/\/\//', '/', $path);
    $base_dir = strpos($path, DIRECTORY_SEPARATOR) == strlen($path) - 1 ? $path: $path . DIRECTORY_SEPARATOR; //基目录

    if (is_file($path)) {
        $localName = str_replace($base_dir, '', $path);
        $zip->addFile($path, $localName);
        $zip->close();

        return $zipFile;
    }

    function addFileToZip($path, &$zip, &$base_dir) {
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $targetFile = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($targetFile)) {//条目是文件
                $localName = str_replace($base_dir, '', $targetFile);
                //var_dump($localName);var_dump($targetFile);
                $zip->addFile($targetFile, $localName);
            } else if (is_dir($targetFile)) {
                addFileToZip($targetFile, $zip, $base_dir);
                $localName = str_replace($base_dir, '', $targetFile);
                //var_dump($localName);
                $zip->addEmptyDir($localName);
            }
        }

        closedir($handle);
    }

    addFileToZip($path, $zip, $base_dir);
    $zip->close();

    return $zipFile;
}

/**
 * 解压zip文件
 * @param String $zipFile 压缩包路径
 * @param string $unzipPath 解压路径
 */
function x_unzip($zipFile, $unzipPath = '.')
{
    $zip = new \ZipArchive();
    if ($zip->open($zipFile) == true) {
        //将压缩文件解压到指定的目录下
        $zip->extractTo($unzipPath);
        //关闭zip文档
        $zip->close();
    }
}

//日志输出，用于第三方库统一日志输出，如extend或vendor内的库输出
function file_log($message, $level='debug')
{
    Log::log($level, $message);
}

//金额分显示为元
function money_show($fee)
{
    return number_format($fee / 100, 2);
}

//php获取中文字符拼音首字母
function get_first_pinyin($str)
{
    $py  = new \app\common\library\PinYin();
    $str = $py->getFirstLetter($str);
    return strtoupper($str);
}

/**
 * 密码，加密 方式
 * @param 原始密码 $rawPasswd
 * @param 加密key $key
 * @return boolean|string
 */
function encrypt_password($rawPasswd, $key = '')
{
    if (empty($rawPasswd)) {
        return false;
    }

    $passwd = strtolower((string)$rawPasswd);
    if (empty($key)) {
        $key = get_config('password_key', '');
    }

    return strtolower(md5(sha1($passwd) . $key));
}

/**
 * 字符串命名风格转换
 * @param string $name 字符串
 * @param integer $style 映射风格， 0为c风格，1为java风格
 * @param boolean $isField 是否是变量
 * @return string
 */
function parse_name($name, $style = 0, $isField = false)
{
    if ($style) {
        $newName = ucfirst(preg_replace_callback("/_([a-zA-Z])/", function ($r) {return strtoupper($r[1]);}, $name));
        if ($isField) {
            $newName = lcfirst($newName);
        }
        return $newName;
    } else {
        return strtolower(trim(preg_replace_callback("/[A-Z]/", function ($r) {return "_" . $r[0];}, $name), "_"));
    }
}

/**
 * 处理字段映射转换
 * @param array $data 输入数据
 * @param integer $style 映射风格， 0为c风格，1为java风格
 * @return array
 */
function parse_fields($data, $style = 0)
{
    if (empty($data) || !is_array($data)) {
        return $data;
    }

    foreach ($data as $key => $val) {
        $tempVal = $data[$key];
        if ($tempVal && is_array($tempVal)) {
            $tempVal    = parse_fields($tempVal, $style);
            $data[$key] = $tempVal;
        }

        $targetKey = parse_name($key, $style, true);
        if ($key === $targetKey) {
            continue;
        }

        unset($data[$key]);
        $data[$targetKey] = $tempVal;
    }

    return $data;
}

/**
 * 处理字段映射转换，使用指定的字段映射数据
 * @access public
 * @param array $data 输入数据
 * @param array $metaData 指定的字段映射数据,例['uid'=>'uid', 'imageUrl'=>'image_url']
 * @param integer $style 映射风格， 0为c风格，1为java风格
 * @return array
 */
function parse_fields_by_meta($data, $metaData, $style = 1)
{
    // 检查字段映射
    if (empty($metaData)) {
        return $data;
    }

    foreach ($metaData as $key => $val) {
        if (1 == $style) {
            // 读取
            if (array_key_exists($val, $data)) {
                $temp = $data[$val];
                unset($data[$val]);
                $data[$key] = $temp;
            }
        } else {
            if (array_key_exists($key, $data)) {
                $temp = $data[$key];
                unset($data[$key]);
                $data[$val] = $temp;
            }
        }
    }

    return $data;
}

/**
 * 返回当前的毫秒时间戳
 */
function millisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * 获取dateTime格式时间
 * @param string $time 时间戳
 * @param string $format 转换格式
 * @return string 返回指定的日期格式字符串
 */
function date_time($time = '', $format = 'Y-m-d H:i:s')
{
    if (empty($time)) {
        $time = time();
    }
    return date($format, $time);
}

/**
 * @param $object
 * @return array
 */
function obj_to_array(&$object) {
    $arr = json_decode(json_encode( $object),true);
    return  $arr;
}

/**
 * 获取网站配置信息
 * @param  string $key 配置的键名,为空获取全部配置数组
 * @param  string $default 默认值,如果有值且配置值为空返回默认值
 * @return mixed
 */
function get_config($key = '', $default = null)
{
    if (!cache('config') || config('app_debug')) {
        $ConfigModel = new \app\common\model\ConfigModel();
        $config = $ConfigModel->column('value', 'name');
        cache('config', $config, 5 * 60);
    }
    $config = cache('config');
    if (empty($key)) {
        return $config;
    } else {
        if (!isset($config[$key]) && $default !== null) {
            return $default;
        }
        return isset($config[$key]) ? $config[$key] : "null";
    }
}

/**
 * 获取图片信息
 * @param  string|array $id 图片id
 * @return mixed
 * @throws Exception
 */
function get_image($id)
{
    if (is_array($id)) {
        $ImageModel = new \app\common\model\ImageModel();
        return $ImageModel->where('id', 'in', $id)->select();
    } else {
        return \app\common\model\ImageModel::get($id);
    }
}

/**
 * 获取文件信息
 * @param  string|array $id 文件id
 * @return mixed
 * @throws Exception
 */
function get_file($id)
{
    if (is_array($id)) {
        $FileModel = new \app\common\model\FileModel();
        return $FileModel->where('id', 'in', $id)->select();
    } else {
        return \app\common\model\FileModel::get($id);
    }
}

/**
 * 获取主题配置信息
 * @param string $module 模块，默认是cms
 * @return array
 */
function get_theme_config($module='cms')
{
    $prefix = $module == 'cms' ? '' : $module . '_';

    //优先通过数据库配置加载当前主题，无配置时通过config/theme.php加载
    $packageName = get_config($prefix . 'theme_package_name', '');
    if (empty($packageName)) {
        //通过config文件加载当前主题信息
        $config = Config::pull('theme');
        $packageName = $config['package_name'];
    }
    if (empty($packageName)) {
        die('未配置主题信息!');
    }

    //当前主题的存放路径
    $themePath = Env::get('root_path')  . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR;
    if (!file_exists($themePath)) {
        die('主题路径不存在：' . $themePath);
    }
    if (!file_exists($themePath . 'theme.php')) {
        die('主题配置文件不存在：' . $themePath . 'theme.php');
    }

    //读取当前主题详细信息
    $config = require($themePath . 'theme.php');

    return $config;
}

/**
 * 发送邮件
 * @param string $address 邮件地址,支持数组
 * @param string $subject   邮件标题
 * @param string $message 邮件内容
 * @param bool $isHtml 邮件内容
 * @return bool|mix 发送成功返回true,否则返回错误信息
 * @param array $data
 * @throws \mailer\lib\Exception
 */
function send_mail($address = '', $subject = '', $message = '', $isHtml = true, $data = [])
{
    $mailConfig = config('cms.mail') ? config('cms.mail') : [
        'driver'          => 'smtp', // 邮件驱动, 支持 smtp|sendmail|mail 三种驱动
        'host'            => 'smtp.qq.com', // SMTP服务器地址
        'port'            => 465, // SMTP服务器端口号,一般为25
        'addr'            => '', // 发件邮箱地址
        'pass'            => '', // 发件邮箱密码
        'name'            => '', // 发件邮箱名称
        'content_type'    => 'text/html', // 默认文本内容 text/html|text/plain
        'charset'         => 'utf-8', // 默认字符集
        'security'        => 'ssl', // 加密方式 null|ssl|tls, QQ邮箱必须使用ssl
        'sendmail'        => '/usr/sbin/sendmail -bs', // 不适用 sendmail 驱动不需要配置
        'debug'           => true, // 开启debug模式会直接抛出异常, 记录邮件发送日志
        'left_delimiter'  => '{', // 模板变量替换左定界符, 可选, 默认为 {
        'right_delimiter' => '}', // 模板变量替换右定界符, 可选, 默认为 }
        'log_driver'      => '', // 日志驱动类, 可选, 如果启用必须实现静态 public static function write($content, $level = 'debug') 方法
        'log_path'        => Env::get('runtime_path') . 'log' . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR, // 日志路径, 可选, 不配置日志驱动时启用默认日志驱动, 默认路径是 /path/to/tp-mailer/log, 要保证该目录有可写权限, 最好配置自己的日志路径
        'embed'           => 'embed:', // 邮件中嵌入图片元数据标记
    ];
    $userConfig = [
        'host' => get_config('email_host', $mailConfig['host']), //SMTP服务器地址
        'port' => get_config('email_port', $mailConfig['port']), // SMTP服务器端口号,一般为25
        'addr' => get_config('email_addr', $mailConfig['addr']), // 发件邮箱地址
        'pass' => get_config('email_pass', $mailConfig['pass']), // 发件邮箱密码
        'name' => get_config('email_name', $mailConfig['name']), // 发件邮箱名称
    ];
    $mailConfig = array_merge($mailConfig, $userConfig);

    \mailer\lib\Config::init($mailConfig);
    $mailer = \mailer\tp5\Mailer::instance();
    if ($isHtml) {
        $res = $mailer->to($address)
            ->subject($subject)
            ->html($message, $data)
            ->send();
    } else {
        $res = $mailer->to($address)
            ->subject($subject)
            ->line('尊敬的' . get_config('site_name') . '用户')
            ->line($message)
            ->line('-------------------')
            ->line('此邮件由系统自动发送,请勿回复!')
            ->line(get_config('domain_name'))
            ->send();
    }
    if (!$res) {
        return $mailer->getError();
    }

    return true;
}

/**
 * @param string $address
 * @param string $subject
 * @param $tpl
 * @param array $data
 * @return bool|mixed
 * @throws \mailer\lib\Exception
 */
function send_mail_from_tpl($address = '', $subject, $tpl, $data = [])
{
    $mailConfig = config('mail') ? config('mail') : [
        'driver'          => 'smtp', // 邮件驱动, 支持 smtp|sendmail|mail 三种驱动
        'host'            => 'smtp.qq.com', // SMTP服务器地址
        'port'            => 465, // SMTP服务器端口号,一般为25
        'addr'            => '', // 发件邮箱地址
        'pass'            => '', // 发件邮箱密码
        'name'            => '', // 发件邮箱名称
        'content_type'    => 'text/html', // 默认文本内容 text/html|text/plain
        'charset'         => 'utf-8', // 默认字符集
        'security'        => 'ssl', // 加密方式 null|ssl|tls, QQ邮箱必须使用ssl
        'sendmail'        => '/usr/sbin/sendmail -bs', // 不适用 sendmail 驱动不需要配置
        'debug'           => true, // 开启debug模式会直接抛出异常, 记录邮件发送日志
        'left_delimiter'  => '{', // 模板变量替换左定界符, 可选, 默认为 {
        'right_delimiter' => '}', // 模板变量替换右定界符, 可选, 默认为 }
        'log_driver'      => '', // 日志驱动类, 可选, 如果启用必须实现静态 public static function write($content, $level = 'debug') 方法
        'log_path'        => Env::get('runtime_path') . 'log' . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR, // 日志路径, 可选, 不配置日志驱动时启用默认日志驱动, 默认路径是 /path/to/tp-mailer/log, 要保证该目录有可写权限, 最好配置自己的日志路径
        'embed'           => 'embed:', // 邮件中嵌入图片元数据标记
    ];
    $userConfig = [
        'host' => get_config('email_host', $mailConfig['host']), //SMTP服务器地址
        'port' => get_config('email_port', $mailConfig['port']), // SMTP服务器端口号,一般为25
        'addr' => get_config('email_addr', $mailConfig['addr']), // 发件邮箱地址
        'pass' => get_config('email_pass', $mailConfig['pass']), // 发件邮箱密码
        'name' => get_config('email_name', $mailConfig['name']), // 发件邮箱名称
    ];
    $mailConfig = array_merge($mailConfig, $userConfig);

    \mailer\lib\Config::init($mailConfig);
    $mailer = \mailer\tp5\Mailer::instance();

    $content = \think\facade\View::fetch($tpl, $data);

    $res = $mailer->to($address)
        ->subject($subject)
        ->html($content, $data)
        ->send();

    if (!$res) {
        return $mailer->getError();
    }

    return true;
}

/**
 * 时间剩余
 * @param string $start   起始时间 默认当天0点
 * @param string $end     终止时间 默认当天23:59:59
 * @param string $resType 返回格式 int:时间戳,str:年月日格式,默认int
 * @return [type] 剩余时间
 */
function time_left($start = '', $end = '', $resType = 'int')
{
    $start = $start ? strtotime($start) : mktime(0, 0, 0, date('m'), date('d'), date('Y')); //默认当天0点
    $end   = $end ? strtotime($end) : mktime(23, 59, 59, date('m'), date('d'), date('Y')); //默认当天23:59:59

    $dvalue = $end - $start;

    if ($resType == 'str') {
        $result = '';
        $format = [
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒',
        ];

        foreach ($format as $k => $v) {
            if (0 != $result = floor($dvalue / (int) $k)) {
                return $result . $v;
            }
        }
    }

    return $dvalue;
}

//获取当天时间限制
function get_day_limit($date = null,$type = 'int')
{
    if (!$date) {
        $date = date('Y-m-d');
    }
    $timeStart = strtotime($date);
    $timeEnd = $timeStart + 3600*24 -1;
    if ($type == 'date') {
        $timeStart = date('Y-m-d H:i:s',$timeStart);
        $timeEnd = date('Y-m-d H:i:s',$timeEnd);
    }

    $dayLimit = [
        'timeStart' => $timeStart,
        'timeEnd' => $timeEnd,
        'map' => ['between',[$timeStart,$timeEnd]]
    ];
    return $dayLimit;
}

//获取当月时间限制
function get_month_limit($date = null,$type = 'int')
{
    if (!$date) {
        $date = date('Y-m-1');
    }
    $time = strtotime($date);
    $timeStart = date('Y-m-1',$time);
    $timeEnd = date('Y-m-t 23:59:59',$time);
    if ($type == 'int') {
        $timeStart = strtotime($timeStart);
        $timeEnd = strtotime($timeEnd);
    }

    $monthLimit = [
        'timeStart' => $timeStart,
        'timeEnd' => $timeEnd,
        'map' => ['between',[$timeStart,$timeEnd]]
    ];
    return $monthLimit;
}

/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendly_date($sTime, $type = 'normal', $alt = 'false')
{
    if (!$sTime) {
        return '';
    }

    //sTime=源时间，cTime=当前时间，dTime=时间差
    if (!is_numeric($sTime)) {
        $sTime = strtotime($sTime);
    }
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay  = intval(date("z", $cTime)) - intval(date("z", $sTime));
    //$dDay     =   intval($dTime/3600/24);
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if ($type == 'normal') {
        if ($dTime < 60) {
            if ($dTime < 10) {
                return '刚刚'; //by yangjs
            } else {
                return intval(floor($dTime / 10) * 10) . "秒前";
            }
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
            //今天的数据.年份相同.日期相同.
        } elseif ($dYear == 0 && $dDay == 0) {
            //return intval($dTime/3600)."小时前";
            return '今天' . date('H:i', $sTime);
        } elseif ($dYear == 0) {
            return date("m月d日", $sTime);
        } else {
            return date("Y-m-d", $sTime);
        }
    } elseif ($type == 'mohu') {
        if ($dTime < 60) {
            return $dTime . "秒前";
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . "小时前";
        } elseif ($dDay > 0 && $dDay <= 7) {
            return intval($dDay) . "天前";
        } elseif ($dDay > 7 && $dDay <= 30) {
            return intval($dDay / 7) . '周前';
        } elseif ($dDay > 30) {
            return intval($dDay / 30) . '个月前';
        }
        //full: Y-m-d , H:i:s
    } elseif ($type == 'full') {
        return date("Y-m-d H:i:s", $sTime);
    } elseif ($type == 'ymd') {
        return date("Y-m-d", $sTime);
    } else {
        if ($dTime < 60) {
            return $dTime . "秒前";
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . "小时前";
        } elseif ($dYear == 0) {
            return date("Y-m-d H:i:s", $sTime);
        } else {
            return date("Y-m-d H:i:s", $sTime);
        }
    }
}

//菜单激活状态判断
function menu_select($mca = '')
{
    if ($mca == '') {
        return '';
    }
    if (preg_match('/admin\/ShowNav\//', $mca)) {
        $c          = strtolower(preg_replace('/admin\/ShowNav\//', '', $mca));
        $controller = strtolower(request()->controller());
        if ($controller == $c) {
            return 'active';
        }
    } else {
        $str = request()->module() . '/' . request()->controller() . '/' . request()->action();
        if ($str == $mca) {
            return 'active';
        }
    }
    return '';
}

//请求的host
function request_host()
{
    $request = request();
    if ($request->isCli()) {
        return $request->header('x-original-host') ? $request->header('x-original-host') : $request->header('host');
    } else {
        return $host = $request->host();
    }
}

//请求的域名(含scheme)
function request_domain()
{
    $request = request();
    $domain = $request->domain();
    if ($request->isCli()) {
        $scheme = $request->header('scheme') ? $request->header('scheme') : $request->scheme();
        $host = $request->header('x-original-host') ? $request->header('x-original-host') : $request->header('host');
        $domain = $scheme . '://' . $host;
    }
    return $domain;
}

//处理链接地址 添加域名部分
function url_add_domain($url = '')
{
    if (preg_match('/^http.*/', $url)) {
        return $url;
    }
    if (empty($url)) {
        return '';
    }

    $domain = request_domain();

    return $domain . config('view_replace_str.__PUBLIC__') . $url;
}

//从url中获取域名, $root_domain=true时返回根域名
function url_get_domain($url = '', $root_domain=false, &$details=[])
{
    $tokens = explode('/', $url);
    $protocol = str_replace(':', '', $tokens[0]);
    $domain = $tokens[2];
    $baseUrl = $protocol . ':' . '//' . $tokens[2]; //更准确的话: $token[0] . '//' . $tokens[2]
    $path = str_replace($baseUrl, '', $url);

    $details['protocol'] = $protocol;
    $details['domain'] = $domain;
    $details['path'] = $path;
    $details['base_url'] = $baseUrl;

    //解析根域名
    $domainTokens = explode('.', $domain);
    if (count($domainTokens) == 2) {
        $details['root_domain'] = $domain;
    } else if (count($domainTokens) > 2){
        $details['root_domain'] = $domainTokens[count($domainTokens) - 2] . '.' . $domainTokens[count($domainTokens) - 1];
    }

    return $root_domain ? $details['root_domain'] : $details['domain'];
}

//标题截取
function sub_str($str, $start = 0, $length = 17)
{
    if (strlen($str) <= $length) {
        return $str;
    }
    $str = mb_substr($str, $start, $length);
    return $str . '...';
}

//请求参数签名
/**
 * @param $params, 待签名参数
 * @param $secret_key， 参与签名的密钥
 * @param string $sign_type, 签名方式：MD5, RSA ,RSA2
 * @return 返回签名结果
 */
function sign_params($params, $secret_key, $sign_type = 'MD5')
{
    if (empty($params) || empty($secret_key)) {
        return '';
    }

    ksort($params);

    $paramString = '';
    $resultSign = '';
    foreach ($params as $key => $value) {
        if ($key == 'sign') {
            continue;
        }

        $paramString = $paramString . $key . '=' . htmlspecialchars_decode($value);
        $paramString = $paramString . '&';
    }

    $paramString = substr($paramString, 0, strlen($paramString) - 1);
    //Log::debug("paramString:$paramString");

    $sign_type = strtoupper($sign_type);
    if ($sign_type == 'MD5') {
        $paramString = $paramString . $secret_key;
        //Log::log($paramString);
        $resultSign = md5($paramString);
    } else if ($sign_type == 'RSA') {
        //此时 $secret_key为公钥或私钥，当前暂时只支持md5, 暂未实现
    }

    return $resultSign;
}

/**
 * 扩展http_build_query方法，支持$v为null时，不排除
 * @param $query_data
 * @return string
 */
function http_build_query_ext($query_data)
{

    foreach ($query_data as $k => $v) {
        if (is_null($v)) {
            $query_data[$k] = '';
        }
    }

    return http_build_query($query_data);
}

/**
 * 去除文本中，存在跨域攻击的脚本
 * @param $html
 * @param $isEscape, 是否做 htmlspecialchars
 * @return mixed|string
 */
function remove_xss($html, $isEscape=false)
{
    $html = htmlspecialchars_decode($html);Log::info($html);
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

    $searches[]  = '<';
    $replaces[] = '&lt;';
    $searches[]  = '>';
    $replaces[] = '&gt;';

    if ($ms[1]) {
        $allowTags = 'iframe|video|audio|attach|img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|strike|pre|code|embed|section|article';
        $ms[1]     = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searches[] = "&lt;" . $value . "&gt;";

            $value = str_replace('&amp;', '_uch_tmp_str_', $value);
            $value = htmlspecialchars($value);
            $value = str_replace('_uch_tmp_str_', '&amp;', $value);

            $value    = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $skipKeys = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate',
                'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange',
                'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
                'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate',
                'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete',
                'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel',
                'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart',
                'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop',
                'onsubmit', 'onunload', 'javascript', 'script', 'eval', 'behaviour', 'expression');
            $skipStr = implode('|', $skipKeys);
            $value   = preg_replace(array("/($skipStr)/i"), '.', $value);
            if (!preg_match("/^[\/|\s]?($allowTags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    }

    $html = str_replace($searches, $replaces, $html);
    if ($isEscape) {
        $html = htmlspecialchars($html);
    }

    return $html;
}

//************************跟业务逻辑相关******************
//用户数量
function user_count($type = null)
{
    switch ($type) {
        case 'new':
            $today = \think\helper\Time::today();
            $map[] = ['register_time','between', [date_time($today[0]), date_time($today[1])]];
            break;
        case 'vip':
            $map['isvip'] = 1;
            break;
        case 'unactive':
            $map['active'] = 0;
            break;
        default:
            $map = '1=1';
            break;
    }

    $UserModel = new \app\common\model\UserModel();
    $count = $UserModel->cache(30)->where($map)->count('id');
    return $count;
}

//发送系统消息、发送站内信、信息反馈等
function send_message($from, $to, $title, $content='', $type=1)
{
    $data['type'] = $type;
    $data['title'] = $title;
    $data['content'] = $content;
    $data['status'] = \app\common\model\MessageModel::STATUS_SEND;
    $data['from_uid'] = $from;
    $data['to_uid']   = $to;
    $data['is_readed'] = 0;
    $data['send_time'] = date_time();
    $data['create_time'] = date_time();
    \app\common\model\MessageModel::create($data);
}

//消息数量
function message_count($type=0, $status=0, $fromUid=0, $toUid=0)
{
    $where = [];
    if (!empty($type)) {
        $where[] = ['type', '=', $type];
    }
    if (!empty($status)) {
        $where[] = ['status', '=', $status];
    }
    if (!empty($fromUid)) {
        $where[] = ['from_uid', '=', $fromUid];
    }
    if (!empty($toUid)) {
        $where[] = ['to_uid', '=', $toUid];
    }

    $MessageModel = new \app\common\model\MessageModel();
    $count = $MessageModel->cache(10)->where($where)->count('id');
    return $count;
}

include \think\facade\Env::get('app_path') . 'common_business.php';