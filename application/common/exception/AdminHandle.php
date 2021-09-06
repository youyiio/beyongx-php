<?php
/**
 * Created by VSCode.
 * User: cattong
 * Date: 2018-01-17
 * Time: 14:42
 */

namespace app\common\exception;


use Exception;
use think\exception\DbException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Log;
use think\facade\Response;
use think\facade\View;

class AdminHandle extends Handle
{
    public function render(Exception $e)
    {
        $data = [
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'message' => $this->getMessage($e),
            'code'    => $this->getCode($e),
        ];
        $detailError = "[file: {$data['file']}: (line: {$data['line']}) ]  [error code: {$data['code']}] : \n {$data['message']}";

        Log::error('##### ExceptionHandle #### exception class: ' . get_class($e));
        //Log::error('print: [file][line] [error code]: ');
        Log::error($detailError);
        Log::error('Exception stack:');
        Log::error($e->getTraceAsString());

        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return response($e->getMessage(), $e->getStatusCode());
        }

        //model层异常
        if ($e instanceof ModelException) {
            $response = $this->getResponse($e->getModelMessage(), null);
            return $response;
        }

        //数据库操作异常
        if ($e instanceof DbException || $e instanceof PDOException) {
            $response = $this->getResponse($e->getMessage(), null);
            return $response;
        }

        // 其他错误交给系统处理
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
     * @return \think\Response
     */
    protected function getResponse($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $code = 0;
        if (is_numeric($msg)) {
            $code = $msg;
            $msg  = '';
        }
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
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
            $result = View::fetch(Config::get('dispatch_error_tmpl'), $result);
        }
        $response = Response::create($result, $type, 200, $header);

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