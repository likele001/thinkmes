<?php
declare(strict_types=1);

namespace app\common\lib;

use app\admin\model\WemediaConfigModel;
use think\facade\Log;

/**
 * 自媒体 文生图（配图/封面）
 * 从 fa_wemedia_config 读取 ai_image_* 配置，调用 DashScope 通义万相等，保存到 public/uploads/wemedia_cover/
 */
class WemediaImageService
{
    protected string $lastError = '';

    /**
     * 获取图片生成配置（平台级 tenant_id=0）
     */
    public function getConfig(): ?array
    {
        $rows = WemediaConfigModel::where('tenant_id', 0)->select();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r->config_key] = $r->config_value ?? '';
        }
        $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_AI_IMAGE_PROVIDER] ?? '')));
        if ($provider === '') {
            $this->lastError = '未配置图片生成，请在后台「自媒体配置」中配置图片生成（配图/封面）';
            return null;
        }
        $apiKey = trim((string) ($cfg[WemediaConfigModel::KEY_AI_IMAGE_API_KEY] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, '***')) {
            $this->lastError = '未配置图片生成 API Key';
            return null;
        }
        $apiBase = trim((string) ($cfg[WemediaConfigModel::KEY_AI_IMAGE_API_BASE] ?? ''));
        if ($apiBase === '') {
            $apiBase = 'https://dashscope.aliyuncs.com';
        }
        $apiBase = $this->normalizeApiBase($apiBase);
        $model = trim((string) ($cfg[WemediaConfigModel::KEY_AI_IMAGE_MODEL] ?? ''));
        if ($model === '') {
            $model = 'wan2.6-t2i';
        }
        return [
            'provider' => $provider,
            'api_key' => $apiKey,
            'api_base' => $apiBase,
            'model' => $model,
        ];
    }

    /**
     * 文生图：提示词 → 图片文件
     * @param string $prompt 画面描述/提示词
     * @return string|null 成功返回相对路径 uploads/wemedia_cover/YYYYMMDD/xxx.png
     */
    public function textToImage(string $prompt): ?string
    {
        $prompt = mb_substr(trim($prompt), 0, 2100);
        if ($prompt === '') {
            $this->lastError = '提示词为空';
            return null;
        }
        $config = $this->getConfig();
        if ($config === null) {
            return null;
        }
        $provider = $config['provider'];
        $apiBase = $config['api_base'] ?? '';
        $isDashScope = str_contains(strtolower($apiBase), 'dashscope');
        $dashScopeProviders = ['tongyi_wanxiang', 'aliyun', 'dashscope', 'wanxiang'];
        if (in_array($provider, $dashScopeProviders, true) || ($isDashScope && $provider !== '')) {
            return $this->dashscopeTextToImage($prompt, $config);
        }
        $this->lastError = '当前仅支持通义万相（DashScope）文生图。请在「自媒体配置」- 图片生成 中选择「通义万相」或填写 API 地址为 https://dashscope.aliyuncs.com';
        return null;
    }

    /** 只保留协议+主机，去掉路径，避免与固定 path 重复拼接 */
    private function normalizeApiBase(string $apiBase): string
    {
        $apiBase = rtrim($apiBase, '/');
        if (str_starts_with($apiBase, 'http://') || str_starts_with($apiBase, 'https://')) {
            $parsed = parse_url($apiBase);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';
            if ($host !== '') {
                return $scheme . '://' . $host;
            }
        }
        return $apiBase;
    }

    /**
     * DashScope 通义万相 文生图
     */
    private function dashscopeTextToImage(string $prompt, array $config): ?string
    {
        $base = rtrim($config['api_base'], '/');
        $url = $base . '/api/v1/services/aigc/multimodal-generation/generation';
        $body = [
            'model' => $config['model'],
            'input' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [['text' => $prompt]],
                    ],
                ],
            ],
            'parameters' => [
                'size' => '1024*1024',
                'n' => 1,
            ],
        ];
        $headers = [
            'Authorization: Bearer ' . $config['api_key'],
            'Content-Type: application/json',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '') {
            $this->lastError = '文生图请求失败: ' . $err;
            Log::error('WemediaImageService curl: ' . $err);
            return null;
        }
        $data = is_string($raw) ? json_decode($raw, true) : $raw;
        $imageUrl = $this->extractImageUrlFromResponse($data);
        if ($imageUrl === null || $imageUrl === '') {
            $msg = $data['message'] ?? $data['code'] ?? (isset($data['output']['message']) ? $data['output']['message'] : '');
            $this->lastError = $msg ?: '文生图未返回图片';
            Log::warning('WemediaImageService response: ' . substr(is_string($raw) ? $raw : json_encode($raw), 0, 800));
            return null;
        }
        return $this->downloadAndSave($imageUrl);
    }

    /** 从 DashScope/万相 多种返回结构中解析图片 URL */
    private function extractImageUrlFromResponse(?array $data): ?string
    {
        if ($data === null || !is_array($data)) {
            return null;
        }
        $candidates = [
            $data['output']['choices'][0]['message']['content'][0]['image'] ?? null,
            $data['output']['results'][0]['url'] ?? null,
            $data['output']['results'][0]['image_url'] ?? null,
            $data['output']['image_urls'][0] ?? null,
            $data['output']['images'][0]['url'] ?? null,
            $data['output']['images'][0]['image_url'] ?? null,
            $data['content']['image_urls'][0] ?? null,
        ];
        foreach ($candidates as $url) {
            if (is_string($url) && $url !== '' && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                return $url;
            }
        }
        return null;
    }

    private function downloadAndSave(string $imageUrl): ?string
    {
        $root = app()->getRootPath() . 'public/';
        $subDir = 'uploads/wemedia_cover/' . date('Ymd') . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ext = 'png';
        if (preg_match('#\.(jpg|jpeg|webp)#i', $imageUrl, $m)) {
            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
        }
        $filename = date('His') . '_' . uniqid() . '.' . $ext;
        $fullPath = $dir . $filename;
        $ch = curl_init($imageUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $bin = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || $bin === false || $bin === '') {
            $this->lastError = '下载封面图失败';
            return null;
        }
        if (file_put_contents($fullPath, $bin) === false) {
            $this->lastError = '保存封面图失败';
            return null;
        }
        return $subDir . $filename;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
