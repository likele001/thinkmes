<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\common\lib\Auth;
use Closure;
use think\Request;
use think\Response;

/**
 * 小程序 Scanwork 接口与 PC 角色/节点权限一致
 * 需在 AdminAuth 之后执行，根据 config/scanwork_permission 映射校验当前管理员是否拥有对应节点
 */
class ScanworkPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = (int) ($request->adminId ?? 0);
        if ($adminId <= 0) {
            return $this->jsonForbidden('请先登录');
        }

        $superId = (int) (config('auth.super_admin_id') ?? 1);
        if ($adminId === $superId) {
            return $next($request);
        }

        $action = $request->action();
        $map = config('scanwork_permission') ?? [];
        if (!array_key_exists($action, $map)) {
            return $this->jsonForbidden('无权限访问');
        }
        $node = $map[$action];

        // 显式设为 null：仅登录即可（如 checkToken、getScanworkMenu）
        if ($node === null) {
            return $next($request);
        }

        $auth = new Auth();
        $hasPermission = $auth->check($node, $adminId);
        if (!$hasPermission && !str_starts_with($node, 'admin/')) {
            $hasPermission = $auth->check('admin/' . $node, $adminId);
        }
        if (!$hasPermission) {
            return $this->jsonForbidden('无权限访问');
        }

        return $next($request);
    }

    private function jsonForbidden(string $msg): Response
    {
        return json([
            'code' => 0,
            'msg'  => $msg,
            'data' => [],
        ], 403, ['Content-Type' => 'application/json']);
    }
}
