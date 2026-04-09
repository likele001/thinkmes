<?php
declare(strict_types=1);

namespace app\common\lib\prompt;

use think\facade\Db;

/**
 * AI提示词工坊 - 独立AI服务
 * 使用 prompt_ai_config 表，与全局 ai_config 完全隔离
 */
class PromptAiService
{
    private array $config;

    public function __construct()
    {
        $config = Db::name('prompt_ai_config')
            ->where('status', 1)
            ->order('sort asc, id asc')
            ->find();

        if (!$config) {
            throw new \RuntimeException('AI服务未配置，请先在「AI服务配置」中添加并启用一条配置');
        }
        $this->config = $config;
    }

    /**
     * 发送对话请求（非流式）
     * @param string $userPrompt 用户提示词（已填充变量）
     * @param string $systemPrompt 系统提示词（可选）
     * @return array ['content' => string, 'tokens' => int, 'cost_ms' => int]
     */
    public function chat(string $userPrompt, string $systemPrompt = ''): array
    {
        $apiBase  = rtrim($this->config['api_base'] ?: 'https://api.openai.com', '/');
        $apiKey   = $this->config['api_key'];
        $provider = (string) ($this->config['provider'] ?? '');
        $model    = $this->config['model'] ?: 'gpt-3.5-turbo';
        $maxTokens = (int)($this->config['max_tokens'] ?: 2048);
        $temperature = (float)($this->config['temperature'] ?: 0.7);
        $endpointPath = '/v1/chat/completions';
        if ($provider === 'zhipu' || str_ends_with($apiBase, '/api/paas/v4')) {
            $endpointPath = '/chat/completions';
        }

        $messages = [];
        if ($systemPrompt !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        $body = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
        ], JSON_UNESCAPED_UNICODE);

        $start = microtime(true);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiBase . $endpointPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $costMs = (int)((microtime(true) - $start) * 1000);

        if ($response === false || $curlError !== '') {
            throw new \RuntimeException('网络请求失败：' . $curlError);
        }

        $data = json_decode($response, true);
        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? $response;
            throw new \RuntimeException('AI接口错误(' . $httpCode . ')：' . $msg);
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokens  = (int)($data['usage']['total_tokens'] ?? 0);

        return [
            'content' => $content,
            'tokens'  => $tokens,
            'cost_ms' => $costMs,
        ];
    }

    /**
     * 测试连接
     */
    public static function testConnection(array $cfg): array
    {
        $apiBase = rtrim($cfg['api_base'] ?: 'https://api.openai.com', '/');
        $apiKey  = $cfg['api_key'];
        $provider = (string) ($cfg['provider'] ?? '');
        $model   = $cfg['model'] ?: 'gpt-3.5-turbo';
        $endpointPath = '/v1/chat/completions';
        if ($provider === 'zhipu' || str_ends_with($apiBase, '/api/paas/v4')) {
            $endpointPath = '/chat/completions';
        }

        $body = json_encode([
            'model'      => $model,
            'messages'   => [['role' => 'user', 'content' => 'hi']],
            'max_tokens' => 5,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiBase . $endpointPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) return ['ok' => false, 'msg' => '连接失败：' . $err];
        if ($httpCode === 200) return ['ok' => true, 'msg' => '连接成功'];

        $data = json_decode($response, true);
        $msg  = $data['error']['message'] ?? ('HTTP ' . $httpCode);
        return ['ok' => false, 'msg' => '接口返回错误：' . $msg];
    }
}
