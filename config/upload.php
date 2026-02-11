<?php
// 上传配置：本地 / 分片 / OSS 占位
return [
    // 存储驱动：local 本地，oss 阿里云 OSS（占位，需接 SDK）
    'storage'   => env('UPLOAD_STORAGE', 'local'),
    // 单文件最大字节（默认 10MB）
    'max_size'  => (int) env('UPLOAD_MAX_SIZE', 10485760),
    // 分片大小字节（默认 2MB）
    'chunk_size'=> (int) env('UPLOAD_CHUNK_SIZE', 2097152),
    // OSS 占位（接入 SDK 时使用）
    'oss' => [
        'bucket'  => env('OSS_BUCKET', ''),
        'endpoint'=> env('OSS_ENDPOINT', ''),
        'access_key' => env('OSS_ACCESS_KEY', ''),
        'secret_key' => env('OSS_SECRET_KEY', ''),
        'domain'  => env('OSS_DOMAIN', ''),
    ],
];
