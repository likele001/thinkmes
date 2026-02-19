<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Db;

interface CloudDriver
{
    public function upload(string $localPath, string $savePath): array;
}

class AliyunOssDriver implements CloudDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function upload(string $localPath, string $savePath): array
    {
        if (!class_exists(\OSS\OssClient::class)) {
            throw new \RuntimeException('Aliyun OSS SDK not installed');
        }
        $cfg = $this->config;
        $client = new \OSS\OssClient($cfg['access_key'] ?? '', $cfg['secret_key'] ?? '', $cfg['endpoint'] ?? '');
        $client->uploadFile((string)($cfg['bucket'] ?? ''), ltrim($savePath, '/'), $localPath);
        $domain = $cfg['domain'] ?: ($cfg['endpoint'] ?? '');
        $domain = rtrim((string)$domain, '/');
        $url = $domain . '/' . ltrim($savePath, '/');
        return ['url' => $url, 'storage' => 'aliyun'];
    }
}

class QcloudCosDriver implements CloudDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function upload(string $localPath, string $savePath): array
    {
        if (!class_exists(\Qcloud\Cos\Client::class)) {
            throw new \RuntimeException('Qcloud COS SDK not installed');
        }
        $cfg = $this->config;
        $client = new \Qcloud\Cos\Client([
            'region' => $cfg['region'] ?? '',
            'schema' => 'https',
            'credentials' => [
                'secretId' => $cfg['secret_id'] ?? '',
                'secretKey' => $cfg['secret_key'] ?? '',
            ],
        ]);
        $bucket = (string)($cfg['bucket'] ?? '');
        $key = ltrim($savePath, '/');
        $client->upload($bucket, $key, fopen($localPath, 'rb'));
        $domain = $cfg['domain'] ?: ($bucket . '.cos.' . ($cfg['region'] ?? '') . '.myqcloud.com');
        $domain = rtrim((string)$domain, '/');
        $url = $domain . '/' . $key;
        return ['url' => $url, 'storage' => 'qcloud'];
    }
}

class QiniuDriver implements CloudDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function upload(string $localPath, string $savePath): array
    {
        if (!class_exists(\Qiniu\Storage\UploadManager::class)) {
            throw new \RuntimeException('Qiniu SDK not installed');
        }
        $cfg = $this->config;
        $auth = new \Qiniu\Auth($cfg['access_key'] ?? '', $cfg['secret_key'] ?? '');
        $token = $auth->uploadToken((string)($cfg['bucket'] ?? ''));
        $uploadMgr = new \Qiniu\Storage\UploadManager();
        [$ret, $err] = $uploadMgr->putFile($token, ltrim($savePath, '/'), $localPath);
        if ($err !== null) {
            throw new \RuntimeException('Qiniu upload error');
        }
        $domain = rtrim((string)($cfg['domain'] ?? ''), '/');
        $key = $ret['key'] ?? ltrim($savePath, '/');
        $url = $domain . '/' . $key;
        return ['url' => $url, 'storage' => 'qiniu'];
    }
}

class UpyunDriver implements CloudDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function upload(string $localPath, string $savePath): array
    {
        if (!class_exists(\Upyun\Upyun::class)) {
            throw new \RuntimeException('Upyun SDK not installed');
        }
        $cfg = $this->config;
        $serviceConfig = new \Upyun\Config($cfg['bucket'] ?? '', $cfg['operator'] ?? '', $cfg['password'] ?? '');
        $client = new \Upyun\Upyun($serviceConfig);
        $path = '/' . ltrim($savePath, '/');
        $client->write($path, fopen($localPath, 'rb'));
        $domain = rtrim((string)($cfg['domain'] ?? ''), '/');
        $url = $domain . $path;
        return ['url' => $url, 'storage' => 'upyun'];
    }
}

class CloudStorage
{
    public function upload(string $driver, string $localPath, string $savePath, int $tenantId = 0): array
    {
        $base = config('upload', []);
        $conf = $base;
        $driver = strtolower($driver);
        $row = null;
        try {
            $row = Db::name('addon_cloudstorage_config')->where('tenant_id', $tenantId)->find();
            if (!$row && $tenantId !== 0) {
                $row = Db::name('addon_cloudstorage_config')->where('tenant_id', 0)->find();
            }
        } catch (\Throwable $e) {
            $row = null;
        }
        if ($row && is_string($row['config']) && $row['config'] !== '') {
            $cfg = json_decode((string) $row['config'], true) ?: [];
            if (!empty($cfg['driver'])) {
                $driver = strtolower((string) $cfg['driver']);
            }
            if (!empty($cfg['aliyun_bucket'])) {
                $conf['aliyun']['bucket'] = $cfg['aliyun_bucket'];
            }
            if (!empty($cfg['aliyun_endpoint'])) {
                $conf['aliyun']['endpoint'] = $cfg['aliyun_endpoint'];
            }
            if (!empty($cfg['aliyun_access_key'])) {
                $conf['aliyun']['access_key'] = $cfg['aliyun_access_key'];
            }
            if (!empty($cfg['aliyun_secret_key'])) {
                $conf['aliyun']['secret_key'] = $cfg['aliyun_secret_key'];
            }
            if (!empty($cfg['aliyun_domain'])) {
                $conf['aliyun']['domain'] = $cfg['aliyun_domain'];
            }
            if (!empty($cfg['qcloud_bucket'])) {
                $conf['qcloud']['bucket'] = $cfg['qcloud_bucket'];
            }
            if (!empty($cfg['qcloud_region'])) {
                $conf['qcloud']['region'] = $cfg['qcloud_region'];
            }
            if (!empty($cfg['qcloud_secret_id'])) {
                $conf['qcloud']['secret_id'] = $cfg['qcloud_secret_id'];
            }
            if (!empty($cfg['qcloud_secret_key'])) {
                $conf['qcloud']['secret_key'] = $cfg['qcloud_secret_key'];
            }
            if (!empty($cfg['qcloud_domain'])) {
                $conf['qcloud']['domain'] = $cfg['qcloud_domain'];
            }
            if (!empty($cfg['qiniu_bucket'])) {
                $conf['qiniu']['bucket'] = $cfg['qiniu_bucket'];
            }
            if (!empty($cfg['qiniu_access_key'])) {
                $conf['qiniu']['access_key'] = $cfg['qiniu_access_key'];
            }
            if (!empty($cfg['qiniu_secret_key'])) {
                $conf['qiniu']['secret_key'] = $cfg['qiniu_secret_key'];
            }
            if (!empty($cfg['qiniu_domain'])) {
                $conf['qiniu']['domain'] = $cfg['qiniu_domain'];
            }
            if (!empty($cfg['upyun_bucket'])) {
                $conf['upyun']['bucket'] = $cfg['upyun_bucket'];
            }
            if (!empty($cfg['upyun_operator'])) {
                $conf['upyun']['operator'] = $cfg['upyun_operator'];
            }
            if (!empty($cfg['upyun_password'])) {
                $conf['upyun']['password'] = $cfg['upyun_password'];
            }
            if (!empty($cfg['upyun_domain'])) {
                $conf['upyun']['domain'] = $cfg['upyun_domain'];
            }
        }
        $impl = null;
        if ($driver === 'aliyun' || $driver === 'oss') {
            $impl = new AliyunOssDriver($conf['aliyun'] ?? []);
        } elseif ($driver === 'qcloud' || $driver === 'cos' || $driver === 'tencent') {
            $impl = new QcloudCosDriver($conf['qcloud'] ?? []);
        } elseif ($driver === 'qiniu') {
            $impl = new QiniuDriver($conf['qiniu'] ?? []);
        } elseif ($driver === 'upyun') {
            $impl = new UpyunDriver($conf['upyun'] ?? []);
        } else {
            throw new \RuntimeException('Unsupported cloud storage driver');
        }
        return $impl->upload($localPath, $savePath);
    }
}
