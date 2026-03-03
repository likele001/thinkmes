<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Db;
use think\facade\Log;

/**
 * 工厂 AI 通用服务
 * 调用第三方 API，异常捕获完善，接口报错不影响系统
 * 不训练模型，全部使用第三方 API
 */
class AiService
{
    protected int $tenantId = 0;
    protected int $adminId = 0;
    protected string $module = '';
    protected string $action = '';
    protected int $timeout = 30;
    protected string $lastError = '';

    public function __construct(int $tenantId = 0, int $adminId = 0)
    {
        $this->tenantId = $tenantId;
        $this->adminId = $adminId;
    }

    public function setModule(string $module, string $action = ''): self
    {
        $this->module = $module;
        $this->action = $action ?: $module;
        return $this;
    }

    /**
     * 使用指定配置调用 LLM（用于测试）
     */
    public function chatWithConfig(array $config, array $messages, array $options = []): ?string
    {
        if (empty($config['api_key'])) {
            $this->logFail('未配置 API Key');
            return null;
        }
        $start = (int) (microtime(true) * 1000);
        try {
            $url = $this->buildChatUrl($config['api_base'] ?? '');
            $body = [
                'model' => $config['model'] ?? 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.3,
                'max_tokens' => $options['max_tokens'] ?? 100,
            ];
            $resp = $this->httpPost($url, $body, $config['api_key'] ?? '');
            $data = is_string($resp) ? json_decode($resp, true) : $resp;
            $text = $data['choices'][0]['message']['content'] ?? null;
            if ($text === null && isset($data['error'])) {
                $this->lastError = $data['error']['message'] ?? $data['error']['msg'] ?? json_encode($data['error'], JSON_UNESCAPED_UNICODE);
                $this->logFail($this->lastError, $messages);
                return null;
            }
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $costMs = (int) (microtime(true) * 1000) - $start;
            $this->logSuccess($text ?: '', $tokens, $costMs, $messages);
            return $text;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->logFail($e->getMessage(), $messages);
            Log::error('AiService chatWithConfig error: ' . $e->getMessage());
            return null;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * 调用 LLM 接口（OpenAI 兼容）
     */
    public function chat(array $messages, array $options = []): ?string
    {
        $config = $this->getConfig();
        if (!$config) {
            $this->logFail('未配置 AI');
            return null;
        }
        $start = (int) (microtime(true) * 1000);
        try {
            $url = $this->buildChatUrl($config['api_base'] ?? '');
            $body = [
                'model' => $config['model'] ?? 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 2000,
            ];
            $resp = $this->httpPost($url, $body, $config['api_key'] ?? '');
            $data = is_string($resp) ? json_decode($resp, true) : $resp;
            $text = $data['choices'][0]['message']['content'] ?? null;
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $costMs = (int) (microtime(true) * 1000) - $start;
            $this->logSuccess($text ?: '', $tokens, $costMs, $messages);
            return $text;
        } catch (\Throwable $e) {
            $this->logFail($e->getMessage(), $messages);
            Log::error('AiService chat error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 语音转文字（支持百度短语音识别）
     * 百度配置：speech_provider=baidu，speech_api_key 格式为 app_id|api_key|secret_key
     * @param string $audioUrl 音频 URL 或本地路径
     * @param array|null $overrideConfig 测试时传入的配置，覆盖 getConfig()
     */
    public function speechToText(string $audioUrl, ?array $overrideConfig = null): ?string
    {
        $config = $overrideConfig ?? $this->getConfig();
        if (!$config || empty($config['speech_provider'])) {
            $this->logFail('未配置语音识别');
            return null;
        }
        $provider = strtolower(trim((string) ($config['speech_provider'] ?? '')));
        $apiKey = trim((string) ($config['speech_api_key'] ?? ''));
        if (empty($apiKey)) {
            $this->logFail('未配置语音 API Key');
            return null;
        }
        $start = (int) (microtime(true) * 1000);
        try {
            $audioData = $this->fetchAudioData($audioUrl);
            if ($audioData === null || $audioData === '') {
                $this->lastError = '无法获取音频数据，请确认测试音频 URL 可访问';
                $this->logFail($this->lastError);
                return null;
            }
            $format = $this->detectAudioFormat($audioUrl, $audioData);
            $text = null;
            switch ($provider) {
                case 'aliyun':
                    $text = $this->aliyunSpeechToText($apiKey, $audioUrl, $audioData);
                    break;
                case 'tencent':
                    $text = $this->tencentSpeechToText($apiKey, $audioData, $format);
                    break;
                case 'baidu':
                    $text = $this->baiduSpeechToText($apiKey, $audioData, $format);
                    break;
                case 'xfyun':
                    $text = $this->xfyunSpeechToText($apiKey, $audioData, $format);
                    break;
                case 'xfyun_iat':
                    $text = $this->xfyunIatSpeechToText($apiKey, $audioData, $format);
                    break;
            }
            $costMs = (int) (microtime(true) * 1000) - $start;
            if ($text !== null) {
                $this->logSuccess($text, 0, $costMs, ['audio' => $audioUrl]);
                return $text;
            }
            $this->lastError = '语音识别失败';
            $this->logFail($this->lastError);
            return null;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->logFail($e->getMessage());
            Log::error('AiService speechToText error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 获取音频二进制数据（支持 URL 或本地路径）
     */
    protected function fetchAudioData(string $audioUrl): ?string
    {
        $audioUrl = trim($audioUrl);
        if (empty($audioUrl)) {
            return null;
        }
        if (str_starts_with($audioUrl, '/') || str_starts_with($audioUrl, './')) {
            $root = app()->getRootPath() . 'public';
            $path = $root . (str_starts_with($audioUrl, '/') ? $audioUrl : '/' . ltrim($audioUrl, './'));
            if (is_file($path)) {
                return (string) file_get_contents($path);
            }
            return null;
        }
        if (str_starts_with($audioUrl, 'http://') || str_starts_with($audioUrl, 'https://')) {
            $ch = curl_init($audioUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $data = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            return ($err === '' && $data !== false) ? (string) $data : null;
        }
        return null;
    }

    protected function detectAudioFormat(string $url, string $data): array
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: '');
        if (in_array($ext, ['wav', 'amr', 'm4a', 'mp3', 'ogg', 'opus', 'flac'], true)) {
            return ['format' => $ext, 'rate' => $ext === 'amr' ? 8000 : 16000];
        }
        return ['format' => 'pcm', 'rate' => 16000];
    }

    /**
     * 获取可公网访问的音频 URL（阿里云 Fun-ASR 需要）
     */
    protected function getPublicAudioUrl(string $audioUrl): ?string
    {
        $audioUrl = trim($audioUrl);
        if (str_starts_with($audioUrl, 'http://') || str_starts_with($audioUrl, 'https://')) {
            return $audioUrl;
        }
        if (str_starts_with($audioUrl, '/') || str_starts_with($audioUrl, './')) {
            $path = str_starts_with($audioUrl, '/') ? $audioUrl : '/' . ltrim($audioUrl, './');
            try {
                $domain = \think\facade\Request::domain();
                return rtrim($domain, '/') . $path;
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * 阿里云 Fun-ASR 录音文件识别（需公网可访问的 URL）
     * 配置：speech_api_key 为 DashScope API Key
     */
    protected function aliyunSpeechToText(string $apiKey, string $audioUrl, string $audioData): ?string
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            throw new \RuntimeException('阿里云需配置 DashScope API Key');
        }
        $fileUrl = $this->getPublicAudioUrl($audioUrl);
        if ($fileUrl === null || $fileUrl === '') {
            throw new \RuntimeException('阿里云 Fun-ASR 需要公网可访问的音频 URL，请确保音频已上传到可访问地址');
        }
        $url = 'https://dashscope.aliyuncs.com/api/v1/services/audio/asr/transcription';
        $body = ['model' => 'fun-asr', 'input' => ['file_urls' => [$fileUrl]]];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'X-DashScope-Async: enable',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            throw new \RuntimeException('阿里云请求失败: ' . $err);
        }
        $data = json_decode((string) $resp, true);
        $taskId = $data['output']['task_id'] ?? null;
        if (empty($taskId)) {
            $msg = $data['message'] ?? $data['code'] ?? json_encode($data);
            throw new \RuntimeException('阿里云提交失败: ' . $msg);
        }
        for ($i = 0; $i < 30; $i++) {
            usleep(500000);
            $statusUrl = 'https://dashscope.aliyuncs.com/api/v1/tasks/' . $taskId;
            $ch = curl_init($statusUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
            ]);
            $statusResp = curl_exec($ch);
            curl_close($ch);
            $statusData = json_decode((string) $statusResp, true);
            $taskStatus = $statusData['output']['task_status'] ?? '';
            if ($taskStatus === 'SUCCEEDED') {
                $results = $statusData['output']['results'] ?? [];
                $texts = [];
                foreach ($results as $r) {
                    $transUrl = $r['transcription_url'] ?? '';
                    if ($transUrl !== '') {
                        $ch2 = curl_init($transUrl);
                        curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
                        $transResp = curl_exec($ch2);
                        curl_close($ch2);
                        if ($transResp !== false) {
                            $trans = json_decode((string) $transResp, true);
                            foreach ($trans['transcripts'] ?? [] as $t) {
                                $txt = trim((string) ($t['text'] ?? ''));
                                if ($txt !== '') {
                                    $texts[] = $txt;
                                } else {
                                    foreach ($t['sentences'] ?? [] as $s) {
                                        $texts[] = trim((string) ($s['text'] ?? ''));
                                    }
                                }
                            }
                        }
                    }
                }
                return implode('', $texts) ?: '';
            }
            if ($taskStatus === 'FAILED') {
                $results = $statusData['output']['results'] ?? [];
                $errMsg = $statusData['message'] ?? $statusData['code'] ?? '';
                foreach ($results as $r) {
                    if (($r['subtask_status'] ?? '') === 'FAILED') {
                        $errMsg = ($r['message'] ?? $r['code'] ?? '') ?: $errMsg;
                        break;
                    }
                }
                throw new \RuntimeException('阿里云转写失败: ' . ($errMsg ?: '未知原因'));
            }
        }
        throw new \RuntimeException('阿里云转写超时');
    }

    /**
     * 腾讯云一句话识别（60 秒内短音频）
     * 配置：speech_api_key 格式为 secret_id|secret_key
     */
    protected function tencentSpeechToText(string $apiKey, string $audioData, array $format = []): ?string
    {
        $parts = explode('|', $apiKey, 2);
        $secretId = trim($parts[0] ?? '');
        $secretKey = trim($parts[1] ?? '');
        if ($secretId === '' || $secretKey === '') {
            throw new \RuntimeException('腾讯云需配置 speech_api_key 格式：secret_id|secret_key');
        }
        $fmt = $format['format'] ?? 'pcm';
        $fmtMap = ['wav' => 'wav', 'pcm' => 'pcm', 'mp3' => 'mp3', 'm4a' => 'm4a', 'amr' => 'amr', 'ogg' => 'ogg-opus', 'opus' => 'ogg-opus'];
        $voiceFormat = $fmtMap[$fmt] ?? 'wav';
        $payload = [
            'SubServiceType' => 2,
            'ProjectId' => 0,
            'EngSerViceType' => '16k_zh',
            'SourceType' => 1,
            'VoiceFormat' => $voiceFormat,
            'Data' => base64_encode($audioData),
            'DataLen' => strlen($audioData),
        ];
        $host = 'asr.tencentcloudapi.com';
        $service = 'asr';
        $action = 'SentenceRecognition';
        $version = '2019-06-14';
        $timestamp = time();
        $date = gmdate('Y-m-d', $timestamp);
        $alg = 'TC3-HMAC-SHA256';
        $credentialScope = $date . '/' . $service . '/tc3_request';
        $signedHeaders = 'content-type;host';
        $payloadHash = hash('sha256', json_encode($payload));
        $canonicalRequest = "POST\n/\n\ncontent-type:application/json; charset=utf-8\nhost:{$host}\n\n{$signedHeaders}\n{$payloadHash}";
        $stringToSign = "{$alg}\n{$timestamp}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);
        $kDate = hash_hmac('sha256', $date, 'TC3' . $secretKey, true);
        $kService = hash_hmac('sha256', $service, $kDate, true);
        $kSigning = hash_hmac('sha256', 'tc3_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);
        $auth = "{$alg} Credential={$secretId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $ch = curl_init('https://' . $host . '/');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Host: ' . $host,
                'X-TC-Action: ' . $action,
                'X-TC-Version: ' . $version,
                'X-TC-Timestamp: ' . $timestamp,
                'X-TC-Region: ap-shanghai',
                'Authorization: ' . $auth,
            ],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            throw new \RuntimeException('腾讯云请求失败: ' . $err);
        }
        $data = json_decode((string) $resp, true);
        $result = $data['Response']['Result'] ?? null;
        if ($result !== null) {
            return trim((string) $result);
        }
        $errMsg = $data['Response']['Error']['Message'] ?? json_encode($data);
        throw new \RuntimeException('腾讯云识别失败: ' . $errMsg);
    }

    /**
     * 科大讯飞录音文件转写
     * 配置：speech_api_key 格式为 app_id|api_key|api_secret
     */
    protected function xfyunSpeechToText(string $apiKey, string $audioData, array $format = []): ?string
    {
        $parts = explode('|', $apiKey, 3);
        $appId = trim($parts[0] ?? '');
        $apiKeyVal = trim($parts[1] ?? '');
        $apiSecret = trim($parts[2] ?? '');
        if ($appId === '' || $apiKeyVal === '' || $apiSecret === '') {
            throw new \RuntimeException('讯飞需配置 speech_api_key 格式：app_id|api_key|api_secret');
        }
        $ts = (string) time();
        $baseString = $appId . $ts;
        $md5 = md5($baseString);
        $signa = base64_encode(hash_hmac('sha1', $md5, $apiSecret, true));
        $fileName = 'audio_' . $this->tenantId . '.' . ($format['format'] ?? 'wav');
        $fileSize = strlen($audioData);
        $duration = (int) ceil($fileSize / 32000);
        $uploadUrl = 'https://raasr.xfyun.cn/v2/api/upload?' . http_build_query([
            'appId' => $appId,
            'ts' => $ts,
            'signa' => $signa,
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'duration' => max(1, $duration),
            'language' => 'cn',
        ]);
        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $audioData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/octet-stream',
                'Content-Length: ' . $fileSize,
            ],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            throw new \RuntimeException('讯飞上传失败: ' . $err);
        }
        $uploadData = json_decode((string) $resp, true);
        $orderId = $uploadData['content']['orderId'] ?? null;
        if (empty($orderId)) {
            $code = $uploadData['code'] ?? '';
            $desc = $uploadData['descInfo'] ?? $uploadData['message'] ?? json_encode($uploadData);
            $hint = '';
            if ($code === '26601' || (is_string($desc) && str_contains($desc, 'appId'))) {
                $hint = '；请确认语音 Key 格式为 app_id|api_key|api_secret（竖线分隔），且第一段为控制台「应用 ID」、第二段为 API Key、第三段为 APISecret';
            } elseif (is_string($desc) && (str_contains($desc, 'signa verify fail') || str_contains($desc, 'signa'))) {
                $hint = '；签名验证失败：请确认第三段是「接口密钥」（APISecret，用于签名），不是 API Key。到 讯飞控制台 → 对应应用 → 语音转写 → 服务管理 页面，找到「接口密钥」字段，复制到第三段。格式：应用ID|APIKey|接口密钥';
            }
            throw new \RuntimeException('讯飞上传失败: ' . $desc . $hint);
        }
        for ($i = 0; $i < 60; $i++) {
            usleep(1000000);
            $ts2 = (string) time();
            $baseString2 = $appId . $ts2;
            $signa2 = base64_encode(hash_hmac('sha1', md5($baseString2), $apiSecret, true));
            $getUrl = 'https://raasr.xfyun.cn/v2/api/getResult?' . http_build_query([
                'orderId' => $orderId,
                'appId' => $appId,
                'ts' => $ts2,
                'signa' => $signa2,
            ]);
            $ch2 = curl_init($getUrl);
            curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
            $getResp = curl_exec($ch2);
            curl_close($ch2);
            if ($getResp === false) {
                continue;
            }
            $getData = json_decode((string) $getResp, true);
            $content = $getData['content'] ?? null;
            if ($content === null) {
                continue;
            }
            $orderInfo = $content['orderInfo'] ?? [];
            $status = (int) ($orderInfo['status'] ?? -1);
            if ($status === 4) {
                $orderResult = $content['orderResult'] ?? '';
                if (is_string($orderResult)) {
                    $orderResult = json_decode($orderResult, true);
                }
                $lattice = $orderResult['lattice'] ?? $orderResult['lattice2'] ?? [];
                $texts = [];
                foreach ($lattice as $lat) {
                    $json = $lat['json_1best'] ?? [];
                    if (is_string($json)) {
                        $json = json_decode($json, true);
                    }
                    $st = $json['st'] ?? [];
                    $rt = $st['rt'] ?? [];
                    foreach ($rt as $r) {
                        $ws = $r['ws'] ?? [];
                        foreach ($ws as $w) {
                            $cw = $w['cw'] ?? [];
                            foreach ($cw as $c) {
                                $texts[] = trim((string) ($c['w'] ?? ''));
                            }
                        }
                    }
                }
                return implode('', $texts) ?: null;
            }
            if ($status === -1 || $status === 5) {
                throw new \RuntimeException('讯飞转写失败');
            }
        }
        throw new \RuntimeException('讯飞转写超时');
    }

    /**
     * 讯飞语音听写（流式版，实时/短语音，60 秒内）
     * 使用 WebSocket，鉴权为 APIKey + APISecret（hmac-sha256）
     * 配置：speech_api_key 格式为 app_id|api_key|api_secret（与录音文件转写相同，但需开通「语音听写」服务）
     */
    protected function xfyunIatSpeechToText(string $apiKey, string $audioData, array $format = []): ?string
    {
        $parts = explode('|', $apiKey, 3);
        $appId = trim($parts[0] ?? '');
        $apiKeyVal = trim($parts[1] ?? '');
        $apiSecret = trim($parts[2] ?? '');
        if ($appId === '' || $apiKeyVal === '' || $apiSecret === '') {
            throw new \RuntimeException('讯飞语音听写需配置 app_id|api_key|api_secret（控制台「语音听写」页面的 APPID、APIKey、APISecret）');
        }
        $host = 'iat-api.xfyun.cn';
        $path = '/v2/iat';
        $date = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        $signOrigin = "host: {$host}\ndate: {$date}\nGET {$path} HTTP/1.1";
        $signature = base64_encode(hash_hmac('sha256', $signOrigin, $apiSecret, true));
        $authOrigin = 'api_key="' . $apiKeyVal . '", algorithm="hmac-sha256", headers="host date request-line", signature="' . $signature . '"';
        $authorization = base64_encode($authOrigin);
        $query = http_build_query(['host' => $host, 'date' => $date, 'authorization' => $authorization]);
        $url = "wss://{$host}{$path}?{$query}";

        $pcm = $this->xfyunIatAudioToPcm($audioData, $format);
        if ($pcm === null || $pcm === '') {
            throw new \RuntimeException('讯飞语音听写仅支持 16k/8k 单声道 PCM/WAV，请提供 WAV 或 PCM');
        }
        $rate = (int) ($format['rate'] ?? 16000);
        $formatStr = $rate === 8000 ? 'audio/L16;rate=8000' : 'audio/L16;rate=16000';
        $frameSize = $rate === 8000 ? 640 : 1280;
        $chunks = str_split($pcm, $frameSize);
        $total = count($chunks);
        if ($total === 0) {
            return '';
        }

        $ws = $this->xfyunIatWebSocketConnect($url);
        if (!$ws) {
            throw new \RuntimeException('讯飞语音听写 WebSocket 连接失败，请检查 APIKey/APISecret 及网络');
        }
        $texts = [];
        try {
            $this->xfyunIatSendFrame($ws, [
                'common' => ['app_id' => $appId],
                'business' => ['language' => 'zh_cn', 'domain' => 'iat', 'accent' => 'mandarin'],
                'data' => [
                    'status' => 0,
                    'format' => $formatStr,
                    'encoding' => 'raw',
                    'audio' => base64_encode($chunks[0]),
                ],
            ]);
            for ($i = 1; $i < $total; $i++) {
                $this->xfyunIatSendFrame($ws, [
                    'data' => [
                        'status' => 1,
                        'format' => $formatStr,
                        'encoding' => 'raw',
                        'audio' => base64_encode($chunks[$i]),
                    ],
                ]);
            }
            $this->xfyunIatSendFrame($ws, ['data' => ['status' => 2]]);

            while (($msg = $this->xfyunIatReadFrame($ws)) !== null) {
                $j = json_decode($msg, true);
                if (!$j || ($j['code'] ?? -1) !== 0) {
                    continue;
                }
                $wsList = $j['data']['result']['ws'] ?? [];
                foreach ($wsList as $w) {
                    foreach ($w['cw'] ?? [] as $c) {
                        $texts[] = trim((string) ($c['w'] ?? ''));
                    }
                }
            }
        } finally {
            @fclose($ws);
        }
        return implode('', $texts) ?: null;
    }

    private function xfyunIatAudioToPcm(string $raw, array $format): ?string
    {
        $fmt = strtolower($format['format'] ?? 'pcm');
        if (strlen($raw) < 44) {
            return $raw;
        }
        if ($fmt === 'wav' && substr($raw, 0, 4) === 'RIFF') {
            $raw = substr($raw, 44);
        }
        return $raw;
    }

    private function xfyunIatWebSocketConnect(string $wssUrl): mixed
    {
        $host = parse_url($wssUrl, PHP_URL_HOST);
        $path = parse_url($wssUrl, PHP_URL_PATH);
        $query = parse_url($wssUrl, PHP_URL_QUERY);
        $uri = $path . ($query ? '?' . $query : '');
        $key = base64_encode(random_bytes(16));
        $header = "GET {$uri} HTTP/1.1\r\n";
        $header .= "Host: {$host}\r\n";
        $header .= "Upgrade: websocket\r\n";
        $header .= "Connection: Upgrade\r\n";
        $header .= "Sec-WebSocket-Key: {$key}\r\n";
        $header .= "Sec-WebSocket-Version: 13\r\n";
        $header .= "\r\n";
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $fp = @stream_socket_client('ssl://' . $host . ':443', $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return null;
        }
        stream_set_timeout($fp, 30);
        fwrite($fp, $header);
        $resp = '';
        while (!str_ends_with($resp, "\r\n\r\n")) {
            $resp .= fread($fp, 1024);
            if ($resp === false || strlen($resp) > 8192) {
                fclose($fp);
                return null;
            }
        }
        if (!preg_match('/HTTP\/1\.1 (\d+)/', $resp, $m) || (int) $m[1] !== 101) {
            fclose($fp);
            return null;
        }
        return $fp;
    }

    private function xfyunIatSendFrame($fp, array $data): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $len = strlen($payload);
        $mask = random_bytes(4);
        $frame = "\x81" . chr(0x80 | ($len < 126 ? $len : 126)) . ($len >= 126 ? pack('n', $len) : '') . $mask;
        for ($i = 0; $i < $len; $i++) {
            $frame .= $payload[$i] ^ $mask[$i % 4];
        }
        fwrite($fp, $frame);
    }

    private function xfyunIatReadFrame($fp): ?string
    {
        $h = fread($fp, 2);
        if (strlen($h) < 2) {
            return null;
        }
        $len = ord($h[1]) & 0x7f;
        if ($len === 126) {
            $len = unpack('n', fread($fp, 2))[1];
        } elseif ($len === 127) {
            $len = unpack('J', fread($fp, 8))[1];
        }
        $payload = $len > 0 ? fread($fp, $len) : '';
        return $payload !== false ? $payload : null;
    }

    /**
     * 百度短语音识别（支持 pcm/wav/amr/m4a，16k 或 8k）
     */
    protected function baiduSpeechToText(string $apiKey, string $audioData, array $format = []): ?string
    {
        $parts = explode('|', $apiKey, 3);
        $apiKeyVal = trim($parts[1] ?? '');
        $secretKey = trim($parts[2] ?? '');
        if ($apiKeyVal === '' || $secretKey === '') {
            throw new \RuntimeException('百度语音需配置 speech_api_key 格式：app_id|api_key|secret_key');
        }
        $token = $this->baiduGetAccessToken($apiKeyVal, $secretKey);
        if ($token === '') {
            return null;
        }
        $fmt = $format['format'] ?? 'pcm';
        $rate = (int) ($format['rate'] ?? 16000);
        $len = strlen($audioData);
        $speech = base64_encode($audioData);
        $body = [
            'format' => $fmt,
            'rate' => $rate,
            'channel' => 1,
            'cuid' => 'thinkmes_' . $this->tenantId,
            'token' => $token,
            'speech' => $speech,
            'len' => $len,
        ];
        $url = 'https://vop.baidu.com/server_api';
        $resp = $this->httpPost($url, $body, '');
        $data = is_string($resp) ? json_decode($resp, true) : $resp;
        $errNo = (int) ($data['err_no'] ?? -1);
        if ($errNo !== 0) {
            $errMsg = $data['err_msg'] ?? 'unknown';
            throw new \RuntimeException('百度语音识别错误 ' . $errNo . ': ' . $errMsg);
        }
        $result = $data['result'] ?? [];
        if (empty($result) || !is_array($result)) {
            return '';
        }
        $text = $result[0] ?? '';
        return is_string($text) ? trim($text) : '';
    }

    protected function baiduGetAccessToken(string $apiKey, string $secretKey): string
    {
        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . urlencode($apiKey) . '&client_secret=' . urlencode($secretKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        if ($resp === false) {
            return '';
        }
        $data = json_decode((string) $resp, true);
        return (string) ($data['access_token'] ?? '');
    }

    /**
     * 检查今日调用次数（限流）
     */
    public function checkRateLimit(): bool
    {
        $config = $this->getConfig();
        $limit = (int) ($config['rate_limit_per_day'] ?? 1000);
        if ($limit <= 0) {
            return true;
        }
        $todayStart = strtotime(date('Y-m-d'));
        $count = Db::name('ai_log')
            ->where('tenant_id', $this->tenantId)
            ->where('create_time', '>=', $todayStart)
            ->where('status', 1)
            ->count();
        return $count < $limit;
    }

    protected function getConfig(): ?array
    {
        $row = Db::name('ai_config')
            ->where('tenant_id', $this->tenantId)
            ->where('status', 1)
            ->find();
        return $row ?: null;
    }

    /**
     * 构建 chat completions 请求 URL，避免重复拼接
     */
    protected function buildChatUrl(string $apiBase): string
    {
        $base = rtrim($apiBase ?: 'https://api.openai.com/v1', '/');
        $suffix = '/chat/completions';
        if (substr($base, -strlen($suffix)) === $suffix) {
            return $base;
        }
        return $base . $suffix;
    }

    protected function httpPost(string $url, array $body, string $apiKey): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) {
            throw new \RuntimeException('AI 请求失败: ' . $err);
        }
        if ($httpCode >= 400) {
            $data = is_string($resp) ? json_decode($resp, true) : $resp;
            $msg = $data['error']['message'] ?? $data['error']['msg'] ?? $data['message'] ?? $data['msg'] ?? (string) $resp;
            if (is_array($msg)) {
                $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
            }
            throw new \RuntimeException('API 返回错误 (HTTP ' . $httpCode . '): ' . (mb_strlen((string) $msg) > 200 ? mb_substr((string) $msg, 0, 200) . '...' : $msg));
        }
        return (string) $resp;
    }

    protected function logSuccess(string $response, int $tokens = 0, int $costMs = 0, $request = null): void
    {
        $this->writeLog(1, $response, $tokens, $costMs, '', $request);
    }

    protected function logFail(string $error, $request = null): void
    {
        $this->writeLog(0, '', 0, 0, $error, $request);
    }

    protected function writeLog(int $status, string $response, int $tokens, int $costMs, string $error, $request): void
    {
        try {
            Db::name('ai_log')->insert([
                'tenant_id' => $this->tenantId,
                'admin_id' => $this->adminId,
                'module' => $this->module ?: 'unknown',
                'action' => $this->action ?: 'unknown',
                'request_text' => is_string($request) ? $request : json_encode($request, JSON_UNESCAPED_UNICODE),
                'response_text' => mb_substr($response, 0, 65535),
                'tokens_used' => $tokens,
                'cost_ms' => $costMs,
                'status' => $status,
                'error_msg' => mb_substr($error, 0, 500),
                'create_time' => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AiService writeLog error: ' . $e->getMessage());
        }
    }
}
