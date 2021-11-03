<?php
namespace app\common\exception;

use app\common\library\ResultCode;

class ModelException extends \Exception  {
    protected $modelCode;
    protected $modelMessage;

    public function __construct($modelCode=0, $modelMessage = '') 
    {
        if (is_int($modelCode)) {
            $this->modelCode = $modelCode;
            $this->modelMessage = empty($modelMessage)? config('resultcode.'.$modelCode): $modelMessage;
        } else {//is_string
            $this->ModelCode = ResultCode::E_MODEL_ERROR;
            $this->modelMessage = $modelCode;
            
        }

        parent::__construct($this->modelMessage, $this->modelCode);
    }

    public function getModelCode()
    {
        return $this->modelCode;
    }

    public function getModelMessage()
    {
        return $this->modelMessage;
    }
}
