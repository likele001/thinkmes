<?php
declare(strict_types=1);

namespace app\admin\middleware;

use app\common\lib\Auth;
use Closure;
use think\Request;
use think\Response;

class CheckAuth
{
    protected array $whiteList = [
        'admin/index/login',
        'admin/index/logout',
        'admin/index/captcha',
        'admin/index/error',  // 无权限提示页，避免二次拦截
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->pathinfo();
        // 去掉伪静态后缀，避免 login.html 被误判为 login/html 导致重定向循环
        $suffix = config('route.url_html_suffix');
        if ($suffix && is_string($suffix) && $suffix !== true) {
            $ext = (str_starts_with($suffix, '.') ? '' : '.') . $suffix;
            if (str_ends_with($path, $ext)) {
                $path = substr($path, 0, -strlen($ext));
            }
        }
        $route = 'admin/' . $path;
        $route = strtolower(preg_replace('#/+#', '/', trim($route, '/')));

        // 处理 admin/index 路径，自动重定向到 admin/index/index
        if ($route === 'admin/index') {
            return redirect((string) url('/admin/index/index'));
        }

        if (in_array($route, $this->whiteList, true)) {
            return $next($request);
        }

        try {
            $admin = $request->session('admin_info');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '请先登录', 'data' => []]);
            }
            return redirect((string) url('/admin/index/login'));
        }
        if (empty($admin) || !isset($admin['id'])) {
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '请先登录', 'data' => []]);
            }
            return redirect((string) url('/admin/index/login'));
        }

        $adminId = (int) $admin['id'];
        $superId = (int) (config('auth.super_admin_id') ?? 1);
        if ($adminId === $superId) {
            return $next($request);
        }

        $auth = new Auth();
        $node = $route;
        if (!$auth->check($node, $adminId)) {
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '无权限访问', 'data' => []]);
            }
            return redirect((string) url('/admin/index/error') . '?msg=' . urlencode('无权限访问'));
        }

        return $next($request);
    }
}
