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
        if (!\tenant_ai_available($tenantId ? (int)$tenantId : null)) {
            // 返回 JSON 错误，前端可据此跳转购买或提示
            return json(['code' => 403, 'msg' => 'AI 功能未启用或租户未开通，请联系客服开通。'], 403);
        }
        return $next($request);
    }
}
