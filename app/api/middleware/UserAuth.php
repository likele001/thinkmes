<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\common\model\UserModel;
use Closure;
use think\facade\Cache;
use think\Request;
use think\Response;

/**
 * C端用户 Token 校验：从 Header Authorization 或 query token 取 token，校验后写入 request->userInfo
 */
class UserAuth
{
    /** token 缓存前缀 */
    public const CACHE_PREFIX = 'user_token:';
    /** 默认有效期 7 天 */
    public const TTL = 604800;

    /**
     * 生成并存储 token，返回 token 字符串
     */
    public static function makeToken(int $userId, int $tenantId, int $ttl = self::TTL): string
    {
        $token = bin2hex(random_bytes(32));
        $key   = self::CACHE_PREFIX . $token;
        Cache::set($key, ['user_id' => $userId, 'tenant_id' => $tenantId], $ttl);
        return $token;
    }

    /**
     * 使 token 失效（登出时调用）
     */
    public static function invalidateToken(string $token): void
    {
        Cache::delete(self::CACHE_PREFIX . $token);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        if ($token !== null && $token !== '') {
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

        $userId   = (int) ($payload['user_id'] ?? 0);
        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        if ($userId <= 0) {
            return $this->jsonError('无效登录状态', 401);
        }

        $user = UserModel::where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->find();
        if (!$user) {
            Cache::delete($cacheKey);
            return $this->jsonError('账号已禁用或不存在', 401);
        }

        $request->userInfo = $user->toArray();
        $request->userId   = $userId;
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
