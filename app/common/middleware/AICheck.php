<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Session;

/**
 * 中间件：检查租户是否有权限使用 AI 功能
 */
class AICheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = Session::get('admin.tenant_id') ?: $request->header('tenant_id');
        $reason = \tenant_ai_unavailable_reason($tenantId ? (int) $tenantId : null);
        if ($reason !== null) {
            return json(['code' => 0, 'msg' => $reason, 'data' => []], 200);
        }
        return $next($request);
    }
}
