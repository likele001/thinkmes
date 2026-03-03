<?php
declare(strict_types=1);

namespace app\admin\middleware;

use app\common\lib\Auth;
use Closure;
use think\Request;
use think\Response;

class CheckAuth
{
    /** 路径式入口时返回随机路径，否则空（供登录/错误页跳转用） */
    protected function getAdminEntryPath(): string
    {
        $envFile = root_path() . '.env';
        if (!is_file($envFile)) {
            return '';
        }
        $content = @file_get_contents($envFile);
        if ($content === false || !preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $content, $m)) {
            return '';
        }
        $entry = trim($m[1]);
        if (substr($entry, -4) === '.php') {
            $entry = substr($entry, 0, -4);
        }
        return $entry;
    }

    /** 登录页 URL：路径式入口时为 /随机路径/index/login，否则用 url() */
    protected function getLoginUrl(): string
    {
        $entry = $this->getAdminEntryPath();
        return $entry !== '' ? '/' . $entry . '/index/login' : (string) url('/admin/index/login');
    }

    /** 后台首页 URL（用于 admin 根路径重定向） */
    protected function getIndexUrl(): string
    {
        $entry = $this->getAdminEntryPath();
        return $entry !== '' ? '/' . $entry . '/index/index' : (string) url('/admin/index/index');
    }

    /** 无权限页 URL */
    protected function getErrorUrl(): string
    {
        $entry = $this->getAdminEntryPath();
        $base = $entry !== '' ? '/' . $entry . '/index/error' : (string) url('/admin/index/error');
        return $base;
    }

    protected array $whiteList = [
        'admin/index/login',
        'admin/index/logout',
        'admin/index/captcha',
        'admin/index/error',  // 无权限提示页，避免二次拦截
        'admin/register/index',
        'admin/register/save',
        'admin/ai/config/testaudio',  // 语音识别测试用音频，阿里云需公网拉取
    ];
    protected array $loginOnlyList = [
        // 个人中心：任何已登录管理员可访问
        'admin/profile/index',
        'admin/profile/updateprofile',
        'profile/index',
        'profile/updateprofile',
        // 通用上传接口：只要已登录即可使用（具体页面权限由业务控制）
        'admin/common/upload',
        'admin/common/uploadchunk',
        'admin/common/mergechunks',
        'common/upload',
        'common/uploadchunk',
        'common/mergechunks',
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
        
        // 标准化路由路径：确保以 admin/ 开头，避免双重前缀
        $path = strtolower(preg_replace('#/+#', '/', trim($path, '/')));
        if (!str_starts_with($path, 'admin/')) {
            $route = 'admin/' . $path;
        } else {
            $route = $path;
        }

        // 处理 admin 根路径，自动重定向到后台首页（路径式入口时用 /随机路径/index/index）
        if ($route === 'admin') {
            return redirect($this->getIndexUrl());
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
            return redirect($this->getLoginUrl());
        }
        if (empty($admin) || !isset($admin['id'])) {
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '请先登录', 'data' => []]);
            }
            return redirect($this->getLoginUrl());
        }

        $adminId = (int) $admin['id'];
        $superId = (int) (config('auth.super_admin_id') ?? 1);
        if ($adminId === $superId) {
            return $next($request);
        }
        if (in_array($route, $this->loginOnlyList, true)) {
            return $next($request);
        }

        $auth = new Auth();
        $node = $route;
        
        // 权限检查：尝试两种路由格式
        // 1. 完整格式（含 admin/）：admin/mes/trace_code
        // 2. 去 admin 前缀后的格式：mes/trace_code
        $hasPermission = $auth->check($node, $adminId);
        if (!$hasPermission && str_starts_with($node, 'admin/')) {
            $nodeWithoutAdminPrefix = substr($node, 6); // 去掉 'admin/' 前缀
            $hasPermission = $auth->check($nodeWithoutAdminPrefix, $adminId);
        }
        
        if (!$hasPermission) {
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '无权限访问', 'data' => []]);
            }
            return redirect($this->getErrorUrl() . '?msg=' . urlencode('无权限访问'));
        }

        return $next($request);
    }
}
