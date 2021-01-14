<?php


namespace app\exception;

/**
 * 自定义异常实现类
 * Class CustomerException
 * @package app\exception
 */
class CustomerException extends BaseException
{
    public function __construct($errorCode, $message, $httpCode=200)
    {
        parent::__construct();
        $this->errorCode = $errorCode;
        $this->message = $message;
        $this->httpCode = $httpCode;
    }
}