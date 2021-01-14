<?php
declare (strict_types=1);

namespace app;

use app\exception\CustomerException;
use think\App;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Log;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 验证手机号 或者 邮箱验证码 是否和服务器的一致
     * @param $key
     * @param $code
     * @return bool
     */
    protected function checkCode($key, $code)
    {
        $serverCode = Cache::get($key);
        if ($serverCode == $code) {
            Cache::delete($key);
            return true;
        }
        return false;
    }

    /**
     * 验证用户邮箱是否正确
     * @param $email
     * @throws CustomerException
     */
    protected function validateEmail($email)
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
        if (empty(preg_match($pattern, $email, $matches))) {
            throw new CustomerException(2000, '请填写正确的邮箱', 200);
        }
    }

    /**
     * 验证手机号是否正确
     * @param $email
     * @throws CustomerException
     */
    protected function validatePhone($phone)
    {
        $pattern = '/^1[3-9]\d{9}$/';
        if (empty(preg_match($pattern, $phone, $matches))) {
            throw new CustomerException(2000, '请填写正确的手机号', 200);
        }
    }
}
