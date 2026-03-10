<?php
declare(strict_types=1);

namespace app\common\lib;

use app\admin\model\WemediaConfigModel;
use think\facade\Log;

/**
 * 自媒体 TTS 语音合成（口播配音）
 * 从 fa_wemedia_config 读取 TTS 配置（tenant_id=0），文本→音频文件，保存到 public/uploads/wemedia_audio/
 */
class TtsService
{
    protected string $lastError = '';

    /**
     * 获取 TTS 配置（平台级 tenant_id=0）
     */
    public function getTtsConfig(): ?array
    {
        $rows = WemediaConfigModel::where('tenant_id', 0)->select();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r->config_key] = $r->config_value ?? '';
        }
        $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_TTS_PROVIDER] ?? '')));
        if ($provider === '') {
            $this->lastError = '未配置 TTS，请在后台「自媒体配置」中选择语音合成供应商';
            return null;
        }
        $apiKey = trim((string) ($cfg[WemediaConfigModel::KEY_TTS_API_KEY] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, '***')) {
            $this->lastError = '未配置 TTS API Key';
            return null;
        }
        $apiBase = trim((string) ($cfg[WemediaConfigModel::KEY_TTS_API_BASE] ?? ''));
        if ($apiBase === '') {
            $apiBase = 'https://api.openai.com/v1';
        }
        $voice = trim((string) ($cfg[WemediaConfigModel::KEY_TTS_VOICE] ?? 'alloy'));
        if ($voice === '') {
            $voice = 'alloy';
        }
        return [
            'provider' => $provider,
            'api_key' => $apiKey,
            'api_base' => rtrim($apiBase, '/'),
            'voice' => $voice,
        ];
    }

    /**
     * 文本转语音并保存为文件
     * @param string $text 要合成的文本（建议单次不超过 4096 字符）
     * @return string|null 成功返回相对路径（如 uploads/wemedia_audio/20250305/xxx.mp3），失败返回 null
     */
    public function textToAudio(string $text): ?string
    {
        $text = trim($text);
        if ($text === '') {
            $this->lastError = '文本为空';
            return null;
        }
        $config = $this->getTtsConfig();
        if ($config === null) {
            return null;
        }
        $provider = $config['provider'];
        if ($provider === 'openai') {
            return $this->openaiTts($text, $config);
        }
        $this->lastError = '暂仅支持 OpenAI TTS，其他供应商后续扩展';
        return null;
    }

    /**
     * OpenAI TTS API: POST /v1/audio/speech
     */
    private function openaiTts(string $text, array $config): ?string
    {
        $url = $config['api_base'] . '/audio/speech';
        $body = [
            'model' => 'tts-1',
            'input' => mb_substr($text, 0, 4096),
            'voice' => $config['voice'],
            'response_format' => 'mp3',
            'speed' => 1.0,
        ];
        $headers = [
            'Authorization: Bearer ' . $config['api_key'],
            'Content-Type: application/json',
        ];
        $root = app()->getRootPath() . 'public/';
        $subDir = 'uploads/wemedia_audio/' . date('Ymd') . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = date('His') . '_' . uniqid() . '.mp3';
        $fullPath = $dir . $filename;

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err !== '') {
                $this->lastError = 'TTS 请求失败: ' . $err;
                Log::error('TtsService openaiTts curl: ' . $err);
                return null;
            }
            if ($code !== 200) {
                $msg = is_string($raw) ? $raw : json_encode($raw);
                $dec = json_decode($msg, true);
                if (is_array($dec) && isset($dec['error']['message'])) {
                    $msg = $dec['error']['message'];
                }
                $this->lastError = 'TTS 接口错误(' . $code . '): ' . substr($msg, 0, 200);
                Log::warning('TtsService openaiTts response: ' . $msg);
                return null;
            }
            if ($raw === '' || $raw === false) {
                $this->lastError = 'TTS 返回为空';
                return null;
            }
            if (file_put_contents($fullPath, $raw) === false) {
                $this->lastError = '保存音频文件失败';
                return null;
            }
            return $subDir . $filename;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('TtsService openaiTts: ' . $e->getMessage());
            return null;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
