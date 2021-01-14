<?php


namespace app\exception;


use think\Exception;

/**
 * 自定义异常基类
 * Class BaseException
 * @package app\exception
 */
class BaseException extends Exception
{
    // 状态码 业务错误码
    public $errorCode;
    // 提示信息
    public $message;
    // http错误码
    public $httpCode;
}