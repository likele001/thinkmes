<?php
declare(strict_types=1);

namespace app\common\lib\prompt;

use think\facade\Db;

class PromptMediaService
{
    private array $config;

    public function __construct()
    {
        $config = Db::name('prompt_ai_config')
            ->where('status', 1)
            ->order('sort asc, id asc')
            ->find();
        if (!$config) {
            throw new \RuntimeException('AI服务未配置');
        }
        $this->config = $config;
    }

    private function apiBase(): string
    {
        $base = rtrim((string) ($this->config['api_base'] ?? ''), '/');
        if ($base === '') {
            $base = 'https://open.bigmodel.cn/api/paas/v4';
        }
        if (str_ends_with($base, '/api/paas')) {
            $base .= '/v4';
        }
        return $base;
    }

    private function apiKey(): string
    {
        return (string) ($this->config['api_key'] ?? '');
    }

    private function provider(): string
    {
        return (string) ($this->config['provider'] ?? '');
    }

    private function request(string $method, string $path, ?array $body = null, int $timeout = 120): array
    {
        $url = $this->apiBase() . $path;
        $payload = $body !== null ? json_encode($body, JSON_UNESCAPED_UNICODE) : null;

        $ch = curl_init();
        $headers = [
            'Authorization: Bearer ' . $this->apiKey(),
            'Content-Type: application/json',
        ];

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $methodUpper = strtoupper($method);
        if ($methodUpper === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $payload ?: '{}';
        } else {
            $opts[CURLOPT_CUSTOMREQUEST] = $methodUpper;
        }

        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $err !== '') {
            throw new \RuntimeException('网络请求失败：' . $err);
        }
        $data = json_decode((string) $resp, true);
        if (!is_array($data)) {
            $data = ['raw' => (string) $resp];
        }
        if ($http < 200 || $http >= 300) {
            $msg = $data['error']['message'] ?? $data['message'] ?? ($data['raw'] ?? (string) $resp);
            throw new \RuntimeException('AI接口错误(' . $http . ')：' . $msg);
        }
        return $data;
    }

    public function createImageTask(string $prompt, array $options = []): array
    {
        if ($this->provider() !== 'zhipu') {
            throw new \RuntimeException('当前仅支持智谱AI');
        }
        $model = (string) ($options['model'] ?? ($this->config['image_model'] ?? ''));
        if ($model === '') $model = 'cogview-3-flash';
        $size = (string) ($options['size'] ?? '1280x1280');
        $quality = (string) ($options['quality'] ?? 'hd');

        $data = $this->request('POST', '/images/generations', [
            'model' => $model,
            'prompt' => $prompt,
            'size' => $size,
            'quality' => $quality,
        ], 180);

        return [
            'task_id' => (string) ($data['id'] ?? ''),
            'task_status' => (string) ($data['task_status'] ?? ''),
        ];
    }

    public function createVideoTask(string $prompt, array $options = []): array
    {
        if ($this->provider() !== 'zhipu') {
            throw new \RuntimeException('当前仅支持智谱AI');
        }
        $model = (string) ($options['model'] ?? ($this->config['video_model'] ?? ''));
        if ($model === '') $model = 'cogvideox-flash';
        $duration = (int) ($options['duration'] ?? 5);
        $quality = (string) ($options['quality'] ?? 'speed');
        $withAudio = (bool) ($options['with_audio'] ?? false);
        $size = (string) ($options['size'] ?? '');
        $fps = (int) ($options['fps'] ?? 0);

        $body = [
            'model' => $model,
            'prompt' => $prompt,
            'quality' => $quality,
            'with_audio' => $withAudio,
            'duration' => $duration,
        ];
        if ($size !== '') $body['size'] = $size;
        if ($fps > 0) $body['fps'] = $fps;

        $data = $this->request('POST', '/videos/generations', $body, 180);

        return [
            'task_id' => (string) ($data['id'] ?? ''),
            'task_status' => (string) ($data['task_status'] ?? ''),
        ];
    }

    public function queryTask(string $taskId): array
    {
        if ($this->provider() !== 'zhipu') {
            throw new \RuntimeException('当前仅支持智谱AI');
        }
        $taskId = trim($taskId);
        if ($taskId === '') throw new \RuntimeException('任务ID为空');

        $data = $this->request('GET', '/async-result/' . rawurlencode($taskId), null, 60);
        $status = (string) ($data['task_status'] ?? '');
        $result = $data['result'] ?? $data['data'] ?? $data;

        $imageUrl = '';
        $videoUrl = '';

        if (is_array($result)) {
            $imageUrl = (string) ($result['image_url'] ?? $result['url'] ?? '');
            $videoUrl = (string) ($result['video_url'] ?? $result['url'] ?? '');
            if ($imageUrl === '' && isset($result['images']) && is_array($result['images']) && !empty($result['images'])) {
                $first = $result['images'][0];
                if (is_array($first)) $imageUrl = (string) ($first['url'] ?? '');
                if (is_string($first)) $imageUrl = $first;
            }
            if ($videoUrl === '' && isset($result['videos']) && is_array($result['videos']) && !empty($result['videos'])) {
                $first = $result['videos'][0];
                if (is_array($first)) $videoUrl = (string) ($first['url'] ?? '');
                if (is_string($first)) $videoUrl = $first;
            }
            if ($videoUrl === '' && isset($result['video_result']) && is_array($result['video_result']) && !empty($result['video_result'])) {
                $first = $result['video_result'][0];
                if (is_array($first)) $videoUrl = (string) ($first['url'] ?? '');
                if (is_string($first)) $videoUrl = $first;
            }
            if ($imageUrl === '' && isset($result['image_result']) && is_array($result['image_result']) && !empty($result['image_result'])) {
                $first = $result['image_result'][0];
                if (is_array($first)) $imageUrl = (string) ($first['url'] ?? '');
                if (is_string($first)) $imageUrl = $first;
            }
        }

        $errMsg = (string) ($data['error_msg'] ?? $data['message'] ?? '');
        if ($errMsg === '' && isset($result['error_msg'])) $errMsg = (string) $result['error_msg'];

        return [
            'task_status' => $status,
            'image_url' => $imageUrl,
            'video_url' => $videoUrl,
            'error_msg' => $errMsg,
        ];
    }
}

