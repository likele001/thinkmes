<?php
return [
    \think\middleware\LoadLangPack::class,
    \app\common\middleware\TenantResolve::class,
    \app\common\middleware\TenantWriteGuard::class,
    \app\common\middleware\TenantResourceCheck::class,
    \app\common\middleware\Security::class,
    \app\admin\middleware\CheckAuth::class,
    \app\admin\middleware\AdminEntryUrlRewrite::class,
    // \app\admin\middleware\PermissionsPolicyHeader::class,
];
