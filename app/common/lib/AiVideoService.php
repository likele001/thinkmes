<?php
declare(strict_types=1);

namespace app\common\lib;

use app\admin\model\WemediaConfigModel;
use think\facade\Log;

/**
 * 自媒体 AI 视频生成（图生视频 / 文生视频）
 * 从 fa_wemedia_config 读取 ai_video_* 配置，首帧图需公网 URL（ai_video_base_url）
 */
class AiVideoService
{
    protected string $lastError = '';

    private const POLL_INTERVAL = 15;
    private const POLL_MAX = 24;

    /**
     * 获取 AI 视频配置（平台级 tenant_id=0）
     */
    public function getConfig(): ?array
    {
        $rows = WemediaConfigModel::where('tenant_id', 0)->select();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r->config_key] = $r->config_value ?? '';
        }
        $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_PROVIDER] ?? '')));
        if ($provider === '') {
            $this->lastError = '未配置 AI 视频生成，请在后台「自媒体配置」中选择供应商';
            return null;
        }
        $apiKey = trim((string) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_API_KEY] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, '***')) {
            $this->lastError = '未配置 AI 视频 API Key';
            return null;
        }
        $apiBase = trim((string) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_API_BASE] ?? ''));
        if ($apiBase === '') {
            $apiBase = 'https://dashscope.aliyuncs.com';
        }
        $apiBase = $this->normalizeApiBase($apiBase);
        $model = trim((string) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_MODEL] ?? ''));
        $model = $this->normalizeVideoModel($model);
        $baseUrl = rtrim(trim((string) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_BASE_URL] ?? '')), '/');
        $duration = (int) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_DURATION] ?? 10);
        if ($duration !== 5 && $duration !== 10) {
            $duration = 10;
        }
        $segmentChars = (int) ($cfg[WemediaConfigModel::KEY_AI_VIDEO_SEGMENT_CHARS] ?? 0);
        return [
            'provider' => $provider,
            'api_key' => $apiKey,
            'api_base' => $apiBase,
            'model' => $model,
            'base_url' => $baseUrl,
            'duration' => $duration,
            'segment_chars' => $segmentChars,
        ];
    }

    /**
     * 图生视频：首帧图 + 提示词 → 视频文件
     * @param string $imagePath 封面图相对路径，如 uploads/20250101/xxx.jpg
     * @param string $prompt 画面描述/提示词
     * @param string|null $baseUrl 站点公网地址，用于拼首帧图 URL；为空时使用配置中的 ai_video_base_url
     * @return string|null 成功返回相对路径 uploads/wemedia_video/YYYYMMDD/xxx.mp4
     */
    public function generateFromImage(string $imagePath, string $prompt, ?string $baseUrl = null): ?string
    {
        $config = $this->getConfig();
        if ($config === null) {
            return null;
        }
        $baseUrl = $baseUrl !== null && $baseUrl !== '' ? rtrim($baseUrl, '/') : ($config['base_url'] ?? '');
        if ($baseUrl === '') {
            $this->lastError = '请配置站点公网地址（自媒体配置 → AI 视频生成 → 站点公网地址），用于首帧图 URL';
            return null;
        }
        $imgUrl = $baseUrl . '/' . ltrim($imagePath, '/');
        $provider = $config['provider'];
        $apiBase = $config['api_base'] ?? '';
        $isDashScope = str_contains(strtolower($apiBase), 'dashscope');
        $dashScopeProviders = ['aliyun_video', 'aliyun', 'dashscope', 'wanxiang'];
        if (in_array($provider, $dashScopeProviders, true) || ($isDashScope && $provider !== '')) {
            return $this->aliyunVideoFromImage($imgUrl, $prompt, $config);
        }
        $this->lastError = '当前仅支持阿里云万相（DashScope）图生视频。请在「自媒体配置」- AI 视频生成 中选择「阿里云万相」或填写 API 地址为 https://dashscope.aliyuncs.com';
        return null;
    }

    /**
     * 文生视频：仅提示词 → 视频（无需首帧图），同一接口、T2V 模型
     * @return string|null 成功返回相对路径 uploads/wemedia_video/YYYYMMDD/xxx.mp4
     */
    public function generateFromText(string $prompt): ?string
    {
        $config = $this->getConfig();
        if ($config === null) {
            return null;
        }
        $provider = $config['provider'];
        $apiBase = $config['api_base'] ?? '';
        $isDashScope = str_contains(strtolower($apiBase), 'dashscope');
        $dashScopeProviders = ['aliyun_video', 'aliyun', 'dashscope', 'wanxiang'];
        if (in_array($provider, $dashScopeProviders, true) || ($isDashScope && $provider !== '')) {
            $config['model'] = $this->normalizeT2vModel($config['model'] ?? '');
            return $this->aliyunVideoFromText($prompt, $config);
        }
        $this->lastError = '当前仅支持阿里云万相（DashScope）文生视频。请在「自媒体配置」- AI 视频生成 中选择「阿里云万相」';
        return null;
    }

    /**
     * 文生视频（多段拼接）：将长文案按句/段拆成多段，每段生成 5/10 秒视频后 FFmpeg 拼接成长视频
     * @param string $prompt 完整画面描述/脚本
     * @param int $maxCharsPerSegment 每段最大字符数（约对应一段 5～10 秒），0 表示不拆分、只生成一条
     * @return string|null 成功返回相对路径（单段时为单文件，多段时为拼接后的文件）
     */
    public function generateFromTextSegmented(string $prompt, int $maxCharsPerSegment = 280): ?string
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            $this->lastError = '提示词为空';
            return null;
        }
        if ($maxCharsPerSegment <= 0 || mb_strlen($prompt) <= $maxCharsPerSegment) {
            return $this->generateFromText($prompt);
        }
        $chunks = $this->splitPromptChunks($prompt, $maxCharsPerSegment);
        if (count($chunks) <= 1) {
            return $this->generateFromText($prompt);
        }
        $paths = [];
        $root = app()->getRootPath() . 'public/';
        foreach ($chunks as $i => $segment) {
            $path = $this->generateFromText($segment);
            if ($path === null) {
                $this->lastError = sprintf('第 %d 段生成失败: %s', $i + 1, $this->lastError);
                foreach ($paths as $p) {
                    $full = $root . ltrim($p, '/');
                    if (is_file($full)) {
                        @unlink($full);
                    }
                }
                return null;
            }
            $paths[] = $path;
        }
        $maker = new WemediaVideoMaker();
        $concatPath = $maker->concatVideos($paths);
        foreach ($paths as $p) {
            $full = $root . ltrim($p, '/');
            if (is_file($full)) {
                @unlink($full);
            }
        }
        if ($concatPath === null) {
            $this->lastError = $maker->getLastError();
            return null;
        }
        return $concatPath;
    }

    /**
     * 按句/段拆分文案，每块不超过 maxChars 字符（尽量在句末断句）
     * @return string[]
     */
    private function splitPromptChunks(string $prompt, int $maxChars): array
    {
        $prompt = trim($prompt);
        if ($prompt === '' || $maxChars <= 0) {
            return [];
        }
        $parts = preg_split('/([。！？\n]+)/u', $prompt, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $current = '';
        foreach ($parts as $p) {
            if (mb_strlen($current . $p) <= $maxChars) {
                $current .= $p;
            } else {
                if ($current !== '') {
                    $chunks[] = trim($current);
                    $current = '';
                }
                $rest = $p;
                while (mb_strlen($rest) > $maxChars) {
                    $chunks[] = mb_substr($rest, 0, $maxChars);
                    $rest = mb_substr($rest, $maxChars);
                }
                $current = $rest;
            }
        }
        if ($current !== '') {
            $chunks[] = trim($current);
        }
        return array_values(array_filter($chunks, fn($c) => $c !== ''));
    }

    /**
     * 文生视频（自动分段）：根据配置决定单段生成或多段拼接
     * @return string|null 成功返回相对路径
     */
    public function generateFromTextAuto(string $prompt): ?string
    {
        $config = $this->getConfig();
        if ($config === null) {
            return null;
        }
        $segmentChars = (int) ($config['segment_chars'] ?? 0);
        if ($segmentChars > 0 && mb_strlen(trim($prompt)) > $segmentChars) {
            return $this->generateFromTextSegmented($prompt, $segmentChars);
        }
        return $this->generateFromText($prompt);
    }

    /** DashScope 图生视频（I2V）模型 */
    private const DASHSCOPE_VIDEO_MODELS = [
        'wan2.2-i2v-plus', 'wan2.2-i2v-flash', 'wan2.2-kf2v-flash',
        'wan2.6-i2v-flash', 'wan2.6-i2v', 'wan2.6-i2v-us',
        'wan2.5-i2v-preview', 'wanx2.1-i2v-turbo', 'wanx2.1-i2v-plus',
    ];

    /** DashScope 文生视频（T2V）模型，仅需 prompt 无需首帧图（以阿里云控制台/文档为准） */
    private const DASHSCOPE_T2V_MODELS = [
        'wan2.2-t2v', 'wan2.2-t2v-plus', 'wan2.5-t2v-preview', 'wan2.6-t2v',
        'wan2.2-t2v-turbo', 'wanx2.1-t2v-turbo', 'wanx2.1-t2v-plus',
    ];

    private function normalizeVideoModel(string $model): string
    {
        $model = strtolower(trim($model));
        if ($model !== '' && in_array($model, self::DASHSCOPE_VIDEO_MODELS, true)) {
            return $model;
        }
        return 'wan2.2-i2v-plus';
    }

    private function normalizeT2vModel(string $model): string
    {
        $model = strtolower(trim($model));
        if ($model !== '' && in_array($model, self::DASHSCOPE_T2V_MODELS, true)) {
            return $model;
        }
        return 'wan2.2-t2v';
    }

    /** 只保留协议+主机，避免与固定 path 重复拼接 */
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
     * 阿里云万相 文生视频：仅 prompt，无 img_url，T2V 模型
     * 若当前模型报 Model not exist 则自动用 wan2.6-t2v 再试一次
     */
    private function aliyunVideoFromText(string $prompt, array $config): ?string
    {
        $prompt = mb_substr(trim($prompt), 0, 1500);
        $body = [
            'model' => $config['model'],
            'input' => ['prompt' => $prompt],
            'parameters' => [
                'resolution' => '720P',
                'prompt_extend' => true,
                'duration' => (int) ($config['duration'] ?? 10),
            ],
        ];
        $result = $this->submitVideoTaskAndPoll($config['api_base'], $config['api_key'], $body);
        if ($result !== null) {
            return $result;
        }
        if (str_contains($this->lastError, 'Model not exist') && $config['model'] !== 'wan2.6-t2v') {
            $body['model'] = 'wan2.6-t2v';
            return $this->submitVideoTaskAndPoll($config['api_base'], $config['api_key'], $body);
        }
        return null;
    }

    /**
     * 阿里云万相 图生视频：创建任务 → 轮询 → 下载视频
     */
    private function aliyunVideoFromImage(string $imgUrl, string $prompt, array $config): ?string
    {
        $base = rtrim($config['api_base'], '/');
        $createUrl = $base . '/api/v1/services/aigc/video-generation/video-synthesis';
        $prompt = mb_substr(trim($prompt), 0, 800);
        $body = [
            'model' => $config['model'],
            'input' => [
                'prompt' => $prompt,
                'img_url' => $imgUrl,
            ],
            'parameters' => [
                'resolution' => '480P',
                'prompt_extend' => true,
                'duration' => (int) ($config['duration'] ?? 10),
            ],
        ];
        return $this->submitVideoTaskAndPoll($base, $config['api_key'], $body);
    }

    private function submitVideoTaskAndPoll(string $base, string $apiKey, array $body): ?string
    {
        $createUrl = $base . '/api/v1/services/aigc/video-generation/video-synthesis';
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'X-DashScope-Async: enable',
        ];
        $ch = curl_init($createUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '') {
            $this->lastError = '创建任务失败: ' . $err;
            Log::error('AiVideoService create task curl: ' . $err);
            return null;
        }
        $data = is_string($raw) ? json_decode($raw, true) : $raw;
        $taskId = $data['output']['task_id'] ?? $data['output']['taskId'] ?? $data['task_id'] ?? null;
        if ($taskId === null || $taskId === '') {
            $msg = $data['message'] ?? $data['code'] ?? substr(is_string($raw) ? $raw : json_encode($raw), 0, 200);
            $this->lastError = '创建任务失败: ' . $msg;
            Log::warning('AiVideoService create task: ' . (is_string($raw) ? $raw : json_encode($raw)));
            return null;
        }
        $taskId = (string) $taskId;
        $taskUrl = $base . '/api/v1/tasks/' . urlencode($taskId);
        $authHeader = 'Authorization: Bearer ' . $apiKey;
        $unknownCount = 0;
        $maxUnknownRetries = 4;
        for ($i = 0; $i < self::POLL_MAX; $i++) {
            if ($i > 0) {
                sleep(self::POLL_INTERVAL);
            }
            $ch = curl_init($taskUrl);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => [$authHeader],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
            $result = is_string($raw) ? json_decode($raw, true) : $raw;
            $status = $result['output']['task_status'] ?? $result['output']['status'] ?? 'UNKNOWN';
            if ($status === 'SUCCEEDED') {
                $videoUrl = $result['output']['video_url'] ?? $result['output']['result']['video_url'] ?? '';
                if ($videoUrl === '') {
                    $this->lastError = '任务成功但未返回视频 URL';
                    return null;
                }
                return $this->downloadAndSave($videoUrl);
            }
            if ($status === 'FAILED') {
                $msg = $result['output']['message'] ?? $result['output']['code'] ?? '任务执行失败';
                $this->lastError = $msg;
                return null;
            }
            if ($status === 'CANCELED') {
                $this->lastError = '任务已取消';
                return null;
            }
            if ($status === 'UNKNOWN') {
                $unknownCount++;
                if ($unknownCount >= $maxUnknownRetries) {
                    $this->lastError = '任务查询异常(UNKNOWN)。请确认 API Key 与请求地址属同一地域，且未重复提交任务。';
                    return null;
                }
                continue;
            }
        }
        $this->lastError = '生成超时，请稍后在任务中心查看或重试';
        return null;
    }

    /**
     * 下载视频 URL 到 uploads/wemedia_video/ 并返回相对路径
     */
    private function downloadAndSave(string $videoUrl): ?string
    {
        $root = app()->getRootPath() . 'public/';
        $subDir = 'uploads/wemedia_video/' . date('Ymd') . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = date('His') . '_' . uniqid() . '.mp4';
        $fullPath = $dir . $filename;
        $ch = curl_init($videoUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $bin = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || $bin === false || $bin === '') {
            $this->lastError = '下载视频失败';
            return null;
        }
        if (file_put_contents($fullPath, $bin) === false) {
            $this->lastError = '保存视频文件失败';
            return null;
        }
        return $subDir . $filename;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
