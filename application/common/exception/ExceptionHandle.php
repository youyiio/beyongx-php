<?php

/**
 * Created by VSCode.
 * User: cattong
 * Date: 2017-04-12
 * Time: 18:16
 */
namespace app\common\exception;

use think\Log;
use think\Request;
use think\Response;

use think\Config;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;

use think\View as ViewTemplate;

class ExceptionHandle extends Handle
{

    /**
     * @param \Exception $e
     * @return Response|void
     */
    public function render(\Exception $e)
    {

        $data = [
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'message' => $this->getMessage($e),
            'code'    => $this->getCode($e),
        ];
        $detailError = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";

        Log::error('##### ExceptionHandle #### error code:'. $data['code'] . '; message: '. $e->getMessage());
        Log::error($detailError);

        $message = $e->getMessage();
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            return parent::render($e);
        } else if ($e instanceof ErrorException) {
            $msg = Config::get('result_code.'.$e->getCode());
            $msg = $msg ? ($message? ($msg.':'.$message) : $msg) : $message;
            $msg = $msg . '['. $e->getCode(). ']';

            return $this->errorResponse($msg);
        } else if ($e instanceof ModelException) {
            $msg = Config::get('result_code.' . $e->getCode());
            $msg = $msg ? ($message? ($msg.':'.$message) : $msg) : $message;
            $msg = $msg . '['. $e->getCode(). ']';

            return $this->errorResponse($msg);
        }

        //可以在此交由系统处理
        return parent::render($e);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed     $msg 提示信息
     * @param string    $url 跳转的URL地址
     * @param mixed     $data 返回的数据
     * @param integer   $wait 跳转等待时间
     * @param array     $header 发送的Header信息
     * @return void
     */
    protected function errorResponse($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $code = 0;
        if (is_numeric($msg)) {
            $code = $msg;
            $msg  = '';
        }
        if (is_null($url)) {
            $url = Request::instance()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $result = ViewTemplate::instance(Config::get('template'), Config::get('view_replace_str'))
                ->fetch(Config::get('dispatch_error_tmpl'), $result);
        }
        $response = Response::create($result, $type)->header($header);

        return $response;
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $isAjax = request()->isAjax();
        return $isAjax ? Config::get('default_ajax_return') : Config::get('default_return_type');
    }
}