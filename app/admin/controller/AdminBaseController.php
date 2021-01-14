<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\exception\CustomerException;
use think\App;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Log;
use think\facade\Session;
use think\facade\View;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class AdminBaseController
{
    protected $adminId; // 管理员ID

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
    protected $middleware = ['Check.login'];

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
        $this->adminId = session('admin.id');
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
     * 管理员操作日志记录
     * @param $uid 用户id
     * @param $message 描述信息
     * @param string $module 操作模块
     * @return int|string
     */
    protected function actionLog($uid, $message, $module = 'admin')
    {
        $logData = [
            'user_id' => $uid,
            'action_time' => time(),
            'action_ip' => $this->request->ip(),
            'module' => $module,
            'controller' => $this->request->controller(),
            'action_name' => $this->request->action(),
            'description' => $message
        ];
        return Db::table('huion_action_log')->data($logData)->insert();
    }
}
