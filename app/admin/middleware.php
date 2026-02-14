<?php
return [
    \app\common\middleware\TenantResolve::class,
    \app\common\middleware\TenantResourceCheck::class,
    \app\common\middleware\Security::class,
    \app\admin\middleware\CheckAuth::class,
];
