<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Db;

class WxaCodeService
{
    public static function genAccessToken(string $appid, string $secret): string
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . urlencode($appid) . '&secret=' . urlencode($secret);
        $resp = @file_get_contents($url);
        if (!$resp) return '';
        $data = @json_decode($resp, true);
        if (is_array($data) && isset($data['access_token'])) return (string) $data['access_token'];
        return '';
    }

    public static function genUnlimited(string $accessToken, array $payload): string
    {
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . urlencode($accessToken);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json",
                'content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'timeout' => 15,
            ],
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        if (!$resp) return '';
        return $resp;
    }

    public static function savePng(string $binary, int $tenantId, string $filename): string
    {
        if ($binary === '' || $filename === '') return '';
        $root = app()->getRootPath() . 'public/';
        $subDir = 'uploads/wxacode/' . max(0, $tenantId) . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $fullPath = $dir . $filename;
        @file_put_contents($fullPath, $binary);
        return $subDir . $filename;
    }

    public static function pathToUrl(string $relativePath): string
    {
        if ($relativePath === '') return '';
        $path = ltrim($relativePath, '/');
        if (strpos($path, 'uploads/') !== 0) $path = 'uploads/' . ltrim($path, '/');
        return rtrim((string) request()->domain(), '/') . '/' . $path;
    }
}

