#!/usr/bin/env php
<?php
/**
 * 租户小程序配置（基础功能）应用包：生成可在基础版项目中安装的「Miniapp」应用包
 * 用法：在项目根目录执行  php build/pack_miniapp.php  [版本号]
 * 生成：dist/thinkmes-miniapp-{版本}.zip
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_miniapp.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-miniapp';
$zipPath = $root . '/dist/thinkmes-miniapp-' . $version . '.zip';

echo "项目根目录: {$root}\n";
echo "输出目录: {$outDir}\n";
echo "打包文件: {$zipPath}\n";

function delTree($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        $path = $dir . '/' . $f;
        is_dir($path) ? delTree($path) : @unlink($path);
    }
    rmdir($dir);
}

function copyDirRecursive($src, $dst) {
    if (!is_dir($src)) return;
    @mkdir($dst, 0755, true);
    $dir = opendir($src);
    while (($f = readdir($dir)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $srcPath = $src . '/' . $f;
        $dstPath = $dst . '/' . $f;
        if (is_dir($srcPath)) {
            copyDirRecursive($srcPath, $dstPath);
        } else {
            @copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

if (is_dir($outDir)) {
    delTree($outDir);
}
mkdir($outDir, 0755, true);

@mkdir($outDir . '/app/admin/controller', 0755, true);
@mkdir($outDir . '/app/admin/model', 0755, true);
copy($root . '/app/admin/controller/TenantMiniapp.php', $outDir . '/app/admin/controller/TenantMiniapp.php');
copy($root . '/app/admin/model/TenantMiniappModel.php', $outDir . '/app/admin/model/TenantMiniappModel.php');
copyDirRecursive($root . '/app/admin/view/tenant_miniapp', $outDir . '/app/admin/view/tenant_miniapp');

@mkdir($outDir . '/app/admin/route', 0755, true);
copy($root . '/app/admin/route/app.php', $outDir . '/app/admin/route/app.php');

$sqlFiles = [
    'migrate_add_tenant_miniapp.sql',
    'seed_tenant_miniapp_menu.sql',
];
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

$appManifest = [
    'key' => 'miniapp',
    'title' => '租户小程序配置',
    'description' => '租户后台配置微信小程序 AppID/AppSecret，用于小程序登录与业务对接',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'tenant_miniapp',
    'auth_prefix' => 'admin/tenant/miniapp',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$readme = <<<'TXT'
租户小程序配置 - 应用包
======================

用途：
- 为租户提供「小程序配置」后台页面，配置 AppID/AppSecret 等信息

安装：
1. 后台「应用中心」-> 上传应用包 -> 选择本 zip 上传
2. 在卡片中点击安装（会创建表并写入菜单/套餐功能）

说明：
- 安装后入口：租户与用户 -> 租户小程序
- 若租户打开提示“当前套餐未开通小程序功能”，请在「租户套餐 -> 套餐功能」中为该套餐分配“admin/tenant/miniapp”
TXT;
file_put_contents($outDir . '/小程序安装说明.txt', $readme);

@mkdir(dirname($zipPath), 0755, true);
if (file_exists($zipPath)) unlink($zipPath);
$zip = new ZipArchive();
if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    die("无法创建 ZIP: {$zipPath}\n");
}
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($outDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
$baseName = 'thinkmes-miniapp-' . $version;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $path = $file->getRealPath();
        $entry = $baseName . '/' . substr($path, strlen($outDir) + 1);
        $zip->addFile($path, $entry);
    }
}
$zip->close();

echo "打包完成: {$zipPath}\n";
echo "zip 已输出到 dist/。应用中心 -> 上传应用包 -> 选择本 zip 即可安装。\n";

