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
use think\facade\Log;

use app\common\library\ResultCode;

class ApiHandle extends Handle
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

        Log::error('##### ApiHandle #### exception class: ' . get_class($e));
        //Log::error('print: [file][line] [error code]: ');
        Log::error($detailError);
        Log::error('Exception stack:');
        Log::error($e->getTraceAsString());

        $data = [
            "code" => ResultCode::ACTION_FAILED,
            "message" => '',
            "data" => null,
        ];

        if ($e instanceof JwtException) {
            $data["code"] = $e->getCode();
            $data["message"] = $e->getMessage();
            $data["error"] = $e->getJwtError();

            return json($data);
        }

        // 参数验证错误
        if ($e instanceof ValidateException) {
            $data["code"] = $e->getCode() == 0 ?? ResultCode::E_PARAM_VALIDATE_ERROR;
            $data["message"] = $e->getMessage();
            $data["error"] = "E_PARAM_VALIDATE_ERROR";

            return json($data);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            $data["code"] = ResultCode::E_UNKNOW_ERROR;
            $data["message"] = $e->getMessage();
            $data["error"] = "E_UNKNOW_ERROR";

            return json($data);
        }

        //model层异常
        if ($e instanceof ModelException) {
            $data["code"] = $e->getModelCode();
            $data["message"] = $e->getModelMessage();
            $data["error"] = "E_MODEL_ERROR";

            return json($data);
        }

        //数据库操作异常
        if ($e instanceof DbException || $e instanceof PDOException) {
            $data["code"] = ResultCode::E_DB_ERROR;
            $data["message"] = $e->getMessage();

            return json($data);
        }

        $data["code"] = ResultCode::E_UNKNOW_ERROR;
        $data["message"] = $e->getMessage();

        return json($data);
    }


}