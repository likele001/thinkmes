<?php
use app\common\lib\Hook;
use app\common\lib\CloudStorage;
use think\facade\Db;
use think\facade\Session;

Hook::listen('upload_after', function (&$result) {
    if (!is_array($result) || empty($result['path'])) {
        return;
    }

    $tenantId = 0;
    try {
        $req = app()->request ?? null;
        if ($req && isset($req->tenantId)) {
            $tenantId = (int) $req->tenantId;
        }
    } catch (\Throwable $e) {
        $tenantId = 0;
    }
    if ($tenantId === 0) {
        $admin = Session::get('admin_info');
        if (is_array($admin) && isset($admin['tenant_id'])) {
            $tenantId = (int) $admin['tenant_id'];
        }
    }

    $driver = '';
    try {
        $configRow = Db::name('addon_cloudstorage_config')->where('tenant_id', $tenantId)->find();
        if (!$configRow && $tenantId !== 0) {
            $configRow = Db::name('addon_cloudstorage_config')->where('tenant_id', 0)->find();
        }
    } catch (\Throwable $e) {
        $configRow = null;
    }
    if ($configRow && is_string($configRow['config']) && $configRow['config'] !== '') {
        $cfg = json_decode((string) $configRow['config'], true) ?: [];
        $driver = strtolower((string) ($cfg['driver'] ?? ''));
    }
    if ($driver === '') {
        $driver = strtolower((string) (config('upload.storage') ?? 'local'));
    }
    if ($driver === '' || $driver === 'local') {
        return;
    }

    $root = app()->getRootPath() . 'public/uploads/';
    $localPath = $root . ltrim((string) $result['path'], '/');

    if (!is_file($localPath)) {
        return;
    }

    $savePath = (string) $result['path'];
    if ($tenantId > 0) {
        $savePath = 'tenant_' . $tenantId . '/' . ltrim($savePath, '/');
    }

    $cloud = new CloudStorage();
    try {
        $res = $cloud->upload($driver, $localPath, $savePath);
        if (is_array($res) && isset($res['url'])) {
            $result['url'] = (string) $res['url'];
        }
    } catch (\Throwable $e) {
    }
});
