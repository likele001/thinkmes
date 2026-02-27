<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\admin\model\mes\CustomerModel;
use Closure;
use think\facade\Cache;
use think\Request;
use think\Response;

class CustomerAuth
{
    public const CACHE_PREFIX = 'customer_token:';
    public const TTL = 604800;

    public static function makeToken(int $customerId, int $tenantId, int $ttl = self::TTL): string
    {
        $token = bin2hex(random_bytes(32));
        $key = self::CACHE_PREFIX . $token;
        Cache::set($key, ['customer_id' => $customerId, 'tenant_id' => $tenantId], $ttl);
        return $token;
    }

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
        if ($token === null || $token === '') {
            $token = (string) $request->get('token', '');
        }
        if ($token === '') {
            return $this->jsonError('请先登录', 401);
        }

        $cacheKey = self::CACHE_PREFIX . $token;
        $payload = Cache::get($cacheKey);
        if (!$payload || !is_array($payload)) {
            return $this->jsonError('登录已过期，请重新登录', 401);
        }

        $customerId = (int) ($payload['customer_id'] ?? 0);
        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        if ($customerId <= 0) {
            return $this->jsonError('无效登录状态', 401);
        }

        $customer = CustomerModel::where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->find();
        if (!$customer) {
            Cache::delete($cacheKey);
            return $this->jsonError('账号已禁用或不存在', 401);
        }

        $request->customerInfo = $customer->toArray();
        $request->customerId = $customerId;
        $request->tenantId = $tenantId;
        return $next($request);
    }

    private function jsonError(string $msg, int $httpCode = 401): Response
    {
        return json([
            'code' => 0,
            'msg' => $msg,
            'data' => [],
        ], $httpCode, ['Content-Type' => 'application/json']);
    }
}

