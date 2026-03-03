<?php
declare(strict_types=1);

namespace app\admin\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 路径式后台入口：将响应中的 /admin/ 链接改为 /随机路径/，使后台链接统一走 /随机路径/xxx（经 index.php 伪静态）
 */
class AdminEntryUrlRewrite
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!defined('ADMIN_ENTRY_REQUEST') || !ADMIN_ENTRY_REQUEST) {
            return $response;
        }
        $adminEntry = $this->getAdminEntry();
        if ($adminEntry === '') {
            return $response;
        }
        $search = '/admin/';
        $replace = '/' . $adminEntry . '/';
        // 错误形态：/admin/随机路径/xxx（如 ThinkPHP url() 带 root 时），改为 /随机路径/xxx
        $searchWrong = '/admin/' . $adminEntry . '/';
        $replaceWrong = '/' . $adminEntry . '/';
        $contentType = $response->getHeader('Content-Type');
        if (is_array($contentType)) {
            $contentType = $contentType[0] ?? '';
        }
        $contentType = (string) $contentType;
        $data = $response->getData();
        if (is_string($data)) {
            $data = str_replace($searchWrong, $replaceWrong, $data);
            $data = str_replace($search, $replace, $data);
            return $response->data($data);
        }
        if (stripos($contentType, 'application/json') !== false && is_array($data)) {
            $data = $this->replaceUrlsInArray($data, $searchWrong, $replaceWrong);
            $response->data($this->replaceUrlsInArray($data, $search, $replace));
        }
        return $response;
    }

    private function getAdminEntry(): string
    {
        $envFile = root_path() . '.env';
        if (!is_file($envFile)) {
            return '';
        }
        $content = @file_get_contents($envFile);
        if ($content === false || !preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $content, $m)) {
            return '';
        }
        $entry = trim($m[1]);
        if (substr($entry, -4) === '.php') {
            $entry = substr($entry, 0, -4);
        }
        return $entry;
    }

    private function replaceUrlsInArray(array $arr, string $search, string $replace): array
    {
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                $arr[$k] = str_replace($search, $replace, $v);
            } elseif (is_array($v)) {
                $arr[$k] = $this->replaceUrlsInArray($v, $search, $replace);
            }
        }
        return $arr;
    }
}
