<?php
// API 应用中间件：跨域 + 租户解析 + 资源检查
return [
    \think\middleware\AllowCrossDomain::class,
    \app\common\middleware\TenantResolve::class,
    \app\common\middleware\TenantResourceCheck::class,
];
