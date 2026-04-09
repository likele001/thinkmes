<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\facade\Session;
use think\Request;
use think\Response;

class TenantWriteGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isPost()) {
            return $next($request);
        }
        $admin = Session::get('admin_info');
        if (empty($admin) || !isset($admin['tenant_id']) || (int) $admin['tenant_id'] !== 0) {
            return $next($request);
        }

        $path = ltrim((string) $request->pathinfo(), '/');
        $allowPrefixes = [
            'index/',
            'profile/',
            'tenant/',
            'tenant_package',
            'tenant_order',
            'tenant_package_feature',
            'admin/',
            'role/',
            'auth_rule/',
            'config/',
            'app_center/',
            'attachment/',
            'addon/',
            'payment/config',
        ];
        foreach ($allowPrefixes as $p) {
            if ($p !== '' && strpos($path, $p) === 0) {
                return $next($request);
            }
        }

        $tenantId = $request->param('tenant_id');
        if ($tenantId === null || $tenantId === '') {
            $tenantId = $request->post('tenant_id');
        }
        if ($tenantId === null || $tenantId === '') {
            $tenantId = $request->get('tenant_id');
        }
        $tenantId = (int) $tenantId;
        if ($tenantId <= 0) {
            return json(['code' => 0, 'msg' => '平台写操作必须显式指定 tenant_id', 'data' => []]);
        }
        return $next($request);
    }
}
