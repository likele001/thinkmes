<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\admin\model\AdminModel;
use Closure;
use think\facade\Cache;
use think\Request;
use think\Response;

/**
 * 后端管理小程序：管理员 Token 校验
 * Header Authorization: Bearer <token> 或 query token
 * 校验后写入 request->adminInfo, request->adminId, request->tenantId
 */
class AdminAuth
{
    public const CACHE_PREFIX = 'admin_token:';
    public const TTL = 604800; // 7 天

    public static function makeToken(int $adminId, int $tenantId, int $ttl = self::TTL): string
    {
        $token = bin2hex(random_bytes(32));
        $key   = self::CACHE_PREFIX . $token;
        Cache::set($key, ['admin_id' => $adminId, 'tenant_id' => $tenantId], $ttl);
        return $token;
    }

    public static function invalidateToken(string $token): void
    {
        Cache::delete(self::CACHE_PREFIX . $token);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        if (is_string($token)) {
            $token = preg_replace('/^Bearer\s+/i', '', trim($token));
        }
        if (empty($token)) {
            $token = $request->get('token', '');
        }
        if (empty($token)) {
            return $this->jsonError('请先登录', 401);
        }

        $cacheKey = self::CACHE_PREFIX . $token;
        $payload  = Cache::get($cacheKey);
        if (!$payload || !is_array($payload)) {
            return $this->jsonError('登录已过期，请重新登录', 401);
        }

        $adminId  = (int) ($payload['admin_id'] ?? 0);
        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        if ($adminId <= 0) {
            return $this->jsonError('无效登录状态', 401);
        }

        $admin = AdminModel::where('id', $adminId)->find();
        if (!$admin) {
            Cache::delete($cacheKey);
            return $this->jsonError('账号不存在', 401);
        }
        if ($admin->status !== 1 && $admin->status !== '1') {
            Cache::delete($cacheKey);
            return $this->jsonError('账号已禁用', 401);
        }

        $request->adminInfo = $admin->toArray();
        $request->adminId   = $adminId;
        $request->tenantId = $tenantId;
        return $next($request);
    }

    private function jsonError(string $msg, int $httpCode = 401): Response
    {
        return json([
            'code' => 0,
            'msg'  => $msg,
            'data' => [],
        ], $httpCode, ['Content-Type' => 'application/json']);
    }
}
