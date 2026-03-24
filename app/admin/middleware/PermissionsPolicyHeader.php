<?php
declare(strict_types=1);

namespace app\admin\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 为后台页面添加 Permissions-Policy: unload=(self)，避免 layui 在 iframe 中触发
 * "Permissions policy violation: unload is not allowed in this document" 控制台警告
 */
class PermissionsPolicyHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->header([
            'Permissions-Policy' => 'unload=*',
        ]);
        return $response;
    }
}
