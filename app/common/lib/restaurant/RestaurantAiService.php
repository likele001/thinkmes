<?php
declare(strict_types=1);

namespace app\common\lib\restaurant;

use think\facade\Db;
use think\facade\Log;

class RestaurantAiService
{
    protected int $tenantId = 0;
    protected string $module = '';
    protected string $action = '';
    protected string $lastError = '';

    public function __construct(int $tenantId = 0)
    {
        $this->tenantId = $tenantId;
    }

    public function setModule(string $module, string $action = ''): self
    {
        $this->module = $module;
        $this->action = $action ?: $module;
        return $this;
    }

    public function chat(array $messages, array $options = []): ?string
    {
        $cfg = Db::name('restaurant_ai_config')->where('tenant_id', $this->tenantId)->where('status', 1)->order('id', 'desc')->find();
        if (!$cfg) {
            return null;
        }
        return $this->chatWithConfig([
            'api_key' => (string) ($cfg['api_key'] ?? ''),
            'api_base' => (string) ($cfg['api_base'] ?? ''),
            'model' => (string) ($cfg['model'] ?? ''),
        ], $messages, $options);
    }

    public function chatWithConfig(array $config, array $messages, array $options = []): ?string
    {
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $apiBase = trim((string) ($config['api_base'] ?? ''));
        $model = trim((string) ($config['model'] ?? ''));
        if ($apiBase === '') $apiBase = 'https://api.openai.com/v1';
        if ($model === '') $model = 'gpt-3.5-turbo';
        if ($apiKey === '') return null;

        $url = rtrim($apiBase, '/') . '/chat/completions';
        $body = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];
        $start = (int) (microtime(true) * 1000);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $costMs = (int) (microtime(true) * 1000) - $start;
        if ($err) {
            $this->writeLog(0, '', 0, $costMs, $err, $messages);
            $this->lastError = $err;
            return null;
        }
        $data = json_decode((string) $resp, true);
        $text = $data['choices'][0]['message']['content'] ?? null;
        $tokens = (int) ($data['usage']['total_tokens'] ?? 0);
        if ($text !== null) {
            $this->writeLog(1, $text, $tokens, $costMs, '', $messages);
            return $text;
        }
        $this->writeLog(0, '', 0, $costMs, mb_substr((string) $resp, 0, 200), $messages);
        return null;
    }

    protected function writeLog(int $status, string $response, int $tokens, int $costMs, string $error, $request): void
    {
        try {
            Db::name('restaurant_ai_log')->insert([
                'tenant_id' => $this->tenantId,
                'module' => $this->module ?: 'restaurant_ai',
                'action' => $this->action ?: 'chat',
                'request_text' => json_encode($request, JSON_UNESCAPED_UNICODE),
                'response_text' => mb_substr($response, 0, 65535),
                'tokens_used' => $tokens,
                'cost_ms' => $costMs,
                'status' => $status,
                'error_msg' => mb_substr($error, 0, 500),
                'create_time' => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RestaurantAiService log error: ' . $e->getMessage());
        }
    }
}
