<?php
return [
    \app\common\middleware\TenantResolve::class,
    \app\common\middleware\TenantResourceCheck::class,
    \app\admin\middleware\CheckAuth::class,
];
