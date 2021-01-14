<?php
namespace app\admin\middleware;
use think\facade\Cookie;
use think\facade\Session;
class CheckLoginUser
{
    private $noToken = [
        '/Index/loginPage',
        '/Index/getCaptcha',
        '/Index/loginData'
    ];
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * return Response
     */
    public function handle($request, \Closure $next)
    {
        $controller = $request->controller();
        $action = $request->action();
        if (! in_array( '/' . $controller.'/'.$action , $this->noToken)) {
            // 判定用户是否登录
//            $adminToken = cookie::get('adminToken');
//            if(empty($adminToken) || ! session::get('admin_username')){
//                return redirect(request()->domain().'/admin/Index/loginPage');
//            }
//            cookie::set('adminToken', 7200);
//            session::get($adminToken);
//            session::set($adminToken, session::get($adminToken), 7200);
//            session::set('admin_username', session::get('admin_username'),7200);

            if (empty(session('admin.username'))) {
                return redirect(request()->domain().'/admin/Index/loginPage');
            }
        }

        // 继续执行进入到控制器
        return $next($request);
    }
}