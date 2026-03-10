<?php
declare(strict_types=1);

namespace app\common\lib;

use app\admin\model\WemediaConfigModel;
use think\facade\Log;

/**
 * 数字人播报视频（文本→数字人视频）
 * 从 fa_wemedia_config 读取 digital_human_* 配置
 * 阿里云：SubmitTextTo2DAvatarVideoTask → 轮询 GetVideoTaskInfo → 下载视频
 */
class DigitalHumanService
{
    protected string $lastError = '';

    private const POLL_INTERVAL = 5;
    private const POLL_MAX = 120;

    public function getConfig(): ?array
    {
        $rows = WemediaConfigModel::where('tenant_id', 0)->select();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r->config_key] = $r->config_value ?? '';
        }
        $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_DIGITAL_HUMAN_PROVIDER] ?? '')));
        if ($provider === '') {
            $this->lastError = '未配置数字人，请在后台「自媒体配置」中选择供应商';
            return null;
        }
        $apiKey = trim((string) ($cfg[WemediaConfigModel::KEY_DIGITAL_HUMAN_API_KEY] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, '***')) {
            $this->lastError = '未配置数字人 API Key';
            return null;
        }
        $parts = explode('|', $apiKey, 2);
        $accessKeyId = trim($parts[0] ?? '');
        $accessKeySecret = trim($parts[1] ?? '');
        if ($accessKeyId === '' || $accessKeySecret === '') {
            $this->lastError = '数字人 API Key 格式：access_key_id|access_key_secret（阿里云）';
            return null;
        }
        $tenantId = trim((string) ($cfg[WemediaConfigModel::KEY_DIGITAL_HUMAN_TENANT_ID] ?? ''));
        $appId = trim((string) ($cfg[WemediaConfigModel::KEY_DIGITAL_HUMAN_APP_ID] ?? ''));
        if ($provider === 'aliyun' && ($tenantId === '' || $appId === '')) {
            $this->lastError = '阿里云数字人需配置租户ID与应用ID';
            return null;
        }
        $apiBase = trim((string) ($cfg[WemediaConfigModel::KEY_DIGITAL_HUMAN_API_BASE] ?? ''));
        if ($apiBase === '') {
            $apiBase = 'avatar.cn-hangzhou.aliyuncs.com';
        }
        return [
            'provider' => $provider,
            'access_key_id' => $accessKeyId,
            'access_key_secret' => $accessKeySecret,
            'tenant_id' => $tenantId,
            'app_id' => $appId,
            'api_base' => preg_replace('#^https?://#', '', rtrim($apiBase, '/')),
        ];
    }

    /**
     * 文本生成数字人播报视频
     * @param string $text 播报文本，建议不超过 1000 字
     * @return string|null 成功返回相对路径 uploads/wemedia_video/YYYYMMDD/xxx.mp4
     */
    public function textToVideo(string $text): ?string
    {
        $config = $this->getConfig();
        if ($config === null) {
            return null;
        }
        $text = mb_substr(trim($text), 0, 1000);
        if ($text === '') {
            $this->lastError = '播报文本为空';
            return null;
        }
        if ($config['provider'] === 'aliyun') {
            return $this->aliyun2DTextToVideo($text, $config);
        }
        $this->lastError = '暂仅支持阿里云 2D 数字人，其他供应商后续扩展';
        return null;
    }

    /**
     * 阿里云 2D 数字人：提交任务 → 轮询 GetVideoTaskInfo → 下载
     */
    private function aliyun2DTextToVideo(string $text, array $config): ?string
    {
        $host = $config['api_base'];
        $taskUuid = $this->aliyunSubmit2DTask($host, $config, $text);
        if ($taskUuid === null) {
            return null;
        }
        $videoUrl = $this->aliyunPoll2DTask($host, $config, $taskUuid);
        if ($videoUrl === null) {
            return null;
        }
        return $this->downloadAndSave($videoUrl);
    }

    private function aliyunSubmit2DTask(string $host, array $config, string $text): ?string
    {
        $params = [
            'Action' => 'SubmitTextTo2DAvatarVideoTask',
            'Version' => '2022-01-30',
            'Format' => 'JSON',
            'AccessKeyId' => $config['access_key_id'],
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'SignatureNonce' => uniqid('', true),
            'TenantId' => (string) $config['tenant_id'],
            'App.AppId' => $config['app_id'],
            'Title' => 'wemedia_' . date('YmdHis'),
            'Text' => $text,
        ];
        $params['Signature'] = $this->aliyunSign('GET', $params, $config['access_key_secret']);
        $url = 'https://' . $host . '/?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '') {
            $this->lastError = '提交任务失败: ' . $err;
            return null;
        }
        $data = json_decode((string) $raw, true);
        $taskUuid = $data['Data']['TaskUuid'] ?? null;
        if ($taskUuid === null || $taskUuid === '') {
            $msg = $data['Message'] ?? $data['Code'] ?? substr((string) $raw, 0, 200);
            $this->lastError = '提交任务失败: ' . $msg;
            Log::warning('DigitalHumanService Submit: ' . (string) $raw);
            return null;
        }
        return (string) $taskUuid;
    }

    private function aliyunPoll2DTask(string $host, array $config, string $taskUuid): ?string
    {
        for ($i = 0; $i < self::POLL_MAX; $i++) {
            if ($i > 0) {
                sleep(self::POLL_INTERVAL);
            }
            $params = [
                'Action' => 'GetVideoTaskInfo',
                'Version' => '2022-01-30',
                'Format' => 'JSON',
                'AccessKeyId' => $config['access_key_id'],
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureVersion' => '1.0',
                'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'SignatureNonce' => uniqid('', true),
                'TaskUuid' => $taskUuid,
            ];
            $params['Signature'] = $this->aliyunSign('GET', $params, $config['access_key_secret']);
            $url = 'https://' . $host . '/?' . http_build_query($params);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
            $data = json_decode((string) $raw, true);
            $status = $data['Data']['TaskStatus'] ?? '';
            $statusInt = is_numeric($status) ? (int) $status : -1;
            $statusStr = is_string($status) ? strtoupper($status) : '';
            // 3=成功 / Success；4=失败 / Failed；2=处理中 / Running
            if ($statusInt === 3 || $statusStr === 'SUCCESS' || $statusStr === 'COMPLETED') {
                $videoUrl = $data['Data']['VideoUrl'] ?? $data['Data']['Result']['VideoUrl'] ?? '';
                if ($videoUrl !== '') {
                    return $videoUrl;
                }
            }
            if ($statusInt === 4 || $statusStr === 'FAILED') {
                $this->lastError = $data['Data']['FailReason'] ?? $data['Data']['Message'] ?? '任务失败';
                return null;
            }
        }
        $this->lastError = '生成超时，请稍后重试';
        return null;
    }

    /** 阿里云 RPC 签名 */
    private function aliyunSign(string $method, array $params, string $accessKeySecret): string
    {
        ksort($params);
        $canonicalized = [];
        foreach ($params as $k => $v) {
            if ($k !== 'Signature' && $v !== '') {
                $canonicalized[] = $this->aliyunPercentEncode($k) . '=' . $this->aliyunPercentEncode((string) $v);
            }
        }
        $canonicalizedQueryString = implode('&', $canonicalized);
        $stringToSign = $method . '&' . $this->aliyunPercentEncode('/') . '&' . $this->aliyunPercentEncode($canonicalizedQueryString);
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    private function aliyunPercentEncode(string $s): string
    {
        $s = urlencode($s);
        $s = str_replace(['+', '*'], ['%20', '%2A'], $s);
        $s = preg_replace('/%7E/', '~', $s);
        return $s;
    }

    private function downloadAndSave(string $videoUrl): ?string
    {
        $root = app()->getRootPath() . 'public/';
        $subDir = 'uploads/wemedia_video/' . date('Ymd') . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = 'dh_' . date('His') . '_' . uniqid() . '.mp4';
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
            $this->lastError = '下载数字人视频失败';
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
