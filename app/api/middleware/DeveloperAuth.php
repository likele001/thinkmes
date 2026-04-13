<?php
declare(strict_types=1);

namespace app\api\middleware;

use Closure;
use think\facade\Cache;
use think\facade\Db;
use think\Request;
use think\Response;

/**
 * 开发者 Token 校验：从 Header Authorization 或 query token 或 cookie dev_token 取 token
 */
class DeveloperAuth
{
    public const CACHE_PREFIX = 'dev_token:';
    public const TTL = 604800;

    public static function makeToken(int $developerId, int $ttl = self::TTL): string
    {
        // 生成更安全的token：包含时间戳和开发者ID
        $timestamp = time();
        $random = bin2hex(random_bytes(32));
        $token = hash('sha256', $developerId . ':' . $timestamp . ':' . $random . ':' . microtime(true));
        
        $payload = [
            'developer_id' => $developerId,
            'created_at' => $timestamp,
            'random' => $random,
        ];
        Cache::set(self::CACHE_PREFIX . $token, $payload, $ttl);
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
        if (empty($token)) {
            $token = (string) $request->header('token', '');
        }
        if (empty($token)) {
            $token = (string) $request->get('token', '');
        }
        if (empty($token)) {
            $cookieToken = $request->cookie('dev_token');
            if ($cookieToken !== null && $cookieToken !== '') {
                $token = urldecode((string) $cookieToken);
            } else {
                $token = '';
            }
        }
        
        // 验证token格式：应该是64位十六进制字符串(SHA256)
        if ($token !== '' && !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return $this->jsonError('无效的Token格式', 401);
        }
        
        if (empty($token)) {
            return $this->jsonError('请先登录开发者中心', 401);
        }

        $payload = Cache::get(self::CACHE_PREFIX . $token);
        if (!$payload || !is_array($payload)) {
            return $this->jsonError('登录已过期，请重新登录', 401);
        }

        $developerId = (int) ($payload['developer_id'] ?? 0);
        if ($developerId <= 0) {
            return $this->jsonError('无效登录状态', 401);
        }
        
        // 检查token年龄，如果超过TTL的一半则自动刷新
        $createdAt = (int) ($payload['created_at'] ?? 0);
        if ($createdAt > 0 && (time() - $createdAt) > (self::TTL / 2)) {
            // 自动刷新token
            $newToken = self::makeToken($developerId);
            Cache::delete(self::CACHE_PREFIX . $token);
            // 将新token添加到响应头
            $request->newToken = $newToken;
        }

        $dev = Db::name('market_developer')->where('id', $developerId)->where('status', 1)->find();
        if (!$dev) {
            Cache::delete(self::CACHE_PREFIX . $token);
            return $this->jsonError('开发者账号已禁用或不存在', 401);
        }

        $request->developerInfo = $dev;
        $request->developerId = $developerId;
        $response = $next($request);
        
        // 如果有新token，添加到响应头
        if (isset($request->newToken)) {
            $response->header('X-New-Token', $request->newToken);
            $response->cookie('dev_token', urlencode($request->newToken), [
                'expire' => self::TTL,
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        return $response;
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
