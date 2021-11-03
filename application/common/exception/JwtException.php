<?php
namespace app\common\exception;

use app\common\library\ResultCode;

class JwtException extends \Exception  {
    protected $jwtCode;
    protected $jwtMessage;
    protected $jwtError;

    public function __construct($jwtCode=0, $jwtMessage='', $jwtError='') 
    {
        if (is_int($jwtCode)) {
            $this->jwtCode = $jwtCode;
            $this->jwtMessage = empty($jwtMessage)? config('resultcode.'.$jwtCode): $jwtMessage;
        } else {//is_string
            $this->jwtCode = ResultCode::E_TOKEN_INVALID;
            $this->jwtMessage = $jwtCode;
        }
        $this->jwtError = $jwtError;

        parent::__construct($this->jwtMessage, $this->jwtCode);
    }

    public function getJwtCode()
    {
        return $this->jwtCode;
    }

    public function getJwtMessage()
    {
        return $this->jwtMessage;
    }

    public function getJwtError()
    {
        return $this->jwtError;
    }
}
