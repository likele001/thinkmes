<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\facade\Db;
use think\Request;
use think\Response;

/**
 * 多租户解析：根据域名或 header 解析 tenant_id，写入 request 属性
 */
class TenantResolve
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = 0;
        $host = $request->host();
        $headerTenant = $request->header('X-Tenant-Id');
        if ($headerTenant !== null && $headerTenant !== '') {
            $tenantId = max(0, (int) $headerTenant);
        } elseif ($host && $host !== 'localhost' && $host !== '127.0.0.1') {
            try {
                $row = Db::name('tenant')->where('status', 1)
                    ->where(function ($q) use ($host) {
                        $q->whereEq('domain', $host)
                            ->whereOr('domain', 'like', '%' . $host . '%');
                    })
                    ->find();
                if ($row && isset($row['id'])) {
                    $tenantId = (int) $row['id'];
                    if (isset($row['expire_time']) && $row['expire_time'] !== null && $row['expire_time'] > 0 && $row['expire_time'] < time()) {
                        $tenantId = 0;
                    }
                }
            } catch (\Throwable $e) {
                $tenantId = 0;
            }
        }
        $request->tenantId = $tenantId;
        return $next($request);
    }
}
