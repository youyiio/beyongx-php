<?php
namespace app\common\exception;

use think\exception\DbException;

class ModelException extends DbException  {
    protected $modelCode;
    protected $modelMessage;

    public function __construct($modelCode, $modelMessage = '', $dbException = null) {
        //$dbException = new \Exception();
        if ($dbException != null) {
            parent::__construct($dbException->getMessage(), [], null, $dbException->getCode());
        }

        $this->modelCode = $modelCode;
        $this->modelMessage = empty($modelMessage)? config('resultcode.'.$modelCode): $modelMessage;
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

?>