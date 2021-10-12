<?php
namespace app\common\exception;

use app\common\library\ResultCode;

class LogicException extends \Exception  {
    protected $logicCode;
    protected $logicMessage;

    public function __construct($logicCode=0, $logicMessage='') 
    {
        if (is_int($logicCode)) {
            $this->logicCode = $logicCode;
            $this->logicMessage = empty($logicMessage)? config('resultcode.'.$logicCode): $logicMessage;
        } else {//is_string
            $this->logicCode = ResultCode::ACTION_FAILED;
            $this->logicMessage = $logicCode;
        }
        
        parent::__construct($this->logicMessage, $this->logicCode);
    }

    public function getLogicCode()
    {
        return $this->logicCode;
    }

    public function getLogicMessage()
    {
        return $this->LogicMessage;
    }
}
