<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\exception\HttpException;

/**
 * 全局安全过滤器：XSS 过滤、SQL 注入拦截、速率限制
 */
class Security
{
    /**
     * 排除过滤的字段（如富文本内容）
     */
    protected array $except = [
        'content',
        'description',
        'remark',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. XSS 过滤与输入消毒（分别处理 GET 与 POST）
        $getParams = $this->sanitize($request->get());
        $postParams = $this->sanitize($request->post());
        $request->withGet($getParams);
        $request->withPost($postParams);

        // 2. 基础 WAF：SQL 注入关键词检测（合并后检测）
        $this->checkSqlInjection(array_merge($getParams, $postParams));

        // 3. 敏感操作二次验证或限速（占位，可接入 Redis）
        
        return $next($request);
    }

    /**
     * 递归消毒输入
     */
    protected function sanitize(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }
            if (is_array($value)) {
                $value = $this->sanitize($value);
            } elseif (is_string($value)) {
                // 去除 HTML 标签并转义特殊字符
                $value = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    /**
     * 简单的 SQL 注入检测
     */
    protected function checkSqlInjection(array $data): void
    {
        $pattern = "/(select|insert|update|delete|drop|union|truncate|execute|xp_cmdshell|declare|'|--|\/\*|\*\/)/i";
        foreach ($data as $key => $value) {
            if (is_string($value) && preg_match($pattern, $value)) {
                // 仅作为预警，不强制拦截，除非匹配到高危组合
                if (preg_match("/(union\s+select|select\s+.*\s+from|delete\s+from|drop\s+table)/i", $value)) {
                    throw new HttpException(403, 'Detected potential SQL injection attack');
                }
            }
        }
    }
}
