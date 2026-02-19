<?php
// 上传配置：本地 / 分片 / OSS 占位
return [
    'storage'   => 'local',
    'max_size'  => (int) env('UPLOAD_MAX_SIZE', 52428800),
    'chunk_size'=> (int) env('UPLOAD_CHUNK_SIZE', 2097152),
    'aliyun' => [
        'bucket'  => env('ALIYUN_BUCKET', ''),
        'endpoint'=> env('ALIYUN_ENDPOINT', ''),
        'access_key' => env('ALIYUN_ACCESS_KEY', ''),
        'secret_key' => env('ALIYUN_SECRET_KEY', ''),
        'domain'  => env('ALIYUN_DOMAIN', ''),
    ],
    'qcloud' => [
        'bucket'  => env('QCLOUD_BUCKET', ''),
        'region'  => env('QCLOUD_REGION', ''),
        'secret_id' => env('QCLOUD_SECRET_ID', ''),
        'secret_key' => env('QCLOUD_SECRET_KEY', ''),
        'domain' => env('QCLOUD_DOMAIN', ''),
    ],
    'qiniu' => [
        'bucket' => env('QINIU_BUCKET', ''),
        'access_key' => env('QINIU_ACCESS_KEY', ''),
        'secret_key' => env('QINIU_SECRET_KEY', ''),
        'domain' => env('QINIU_DOMAIN', ''),
        'zone' => env('QINIU_ZONE', ''),
    ],
    'upyun' => [
        'bucket' => env('UPYUN_BUCKET', ''),
        'operator' => env('UPYUN_OPERATOR', ''),
        'password' => env('UPYUN_PASSWORD', ''),
        'domain' => env('UPYUN_DOMAIN', ''),
    ],
];
