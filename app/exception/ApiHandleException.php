<?php


namespace app\exception;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
//use think\Log;
use Exception;
use think\facade\Log;
/**
 * 接管异常处理助手
 * Class ApiHandleException
 * @package app\exception
 */
class ApiHandleException extends Handle
{
    public $errorCode; // 错误码
    public $message; // 错误信息
    public $httpCode; // http code

    public function render($request, Throwable $e): Response
    {
        // 自定义异常
        if ($e instanceof BaseException) {
            $this->errorCode = $e->errorCode;
            $this->message = $e->message;
            $this->httpCode = $e->httpCode;
        } else {
            // 参数验证错误
            if ($e instanceof ValidateException) {
                $this->errorCode = 2000;
                $this->message = $e->getError();
                $this->httpCode = 200;
            } else {
                $this->errorCode = 9999;
                $this->message = '系统错误，请稍后再试!!';
                $this->httpCode = 200;
                $this->recordErrorLog($request->ip(),$e);
            }
            // 系统其他异常 如果是debug模式 交给系统处理
            if (env('APP_DEBUG') == true) {
                return parent::render($request, $e);
            }
        }
        $result = [
            'errorCode' => $this->errorCode,
            'message'  => $this->message
        ];
        return json($result, $this->httpCode);
    }

    /*
     * 将异常写入日志
     */
    private function recordErrorLog($ip,Throwable $e)
    {
        Log::error($ip.$e->getMessage());
//        Log::channel('file')->error('记录错误日志');
//        Log::channel('file')->error($e->getMessage());

//        Log::init([
//            'type'  =>  'File',
//            'path'  =>  './errlog.txt',
//            'level' => ['error']
//        ]);
//        Log::record($e->getTraceAsString());
        //Log::record($e->getMessage(),'error');
    }
}