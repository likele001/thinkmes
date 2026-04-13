#!/usr/bin/env php
<?php
/**
 * CRM 应用打包脚本：生成可在 OA/基础版项目中安装的 CRM 应用包
 * 用法：在项目根目录执行  php build/pack_crm.php
 * 生成：build/thinkmes-crm-1.0.zip，解压到 OA 项目根目录合并后，在后台「应用中心」点击安装 CRM。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_crm.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-crm';
$zipPath = $root . '/dist/thinkmes-crm-' . $version . '.zip';

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

// 1) app/admin: crm 控制器、模型、视图
copyDirRecursive($root . '/app/admin/controller/crm', $outDir . '/app/admin/controller/crm');
copyDirRecursive($root . '/app/admin/model/crm', $outDir . '/app/admin/model/crm');
copyDirRecursive($root . '/app/admin/view/crm', $outDir . '/app/admin/view/crm');

// 2) 独立 CRM 路由（合并到 app/admin/route/crm.php，不覆盖 app.php）
@mkdir(dirname($outDir . '/app/admin/route/crm.php'), 0755, true);
copy($root . '/app/admin/route/crm.php', $outDir . '/app/admin/route/crm.php');

// 3) public/assets/js/backend/crm: CRM 前端 JS 文件
copyDirRecursive($root . '/public/assets/js/backend/crm', $outDir . '/public/assets/js/backend/crm');

// 4) app.json 应用清单（应用中心上传安装用）
$sqlFiles = ['migrate_add_crm_tables.sql', 'migrate_add_crm_sales_order.sql', 'seed_crm_menu.sql'];
$appManifest = [
    'key' => 'crm',
    'title' => 'CRM 客户关系管理',
    'description' => '客户、联系人、商机、合同、跟进、回款、产品、销售订单、报表；可与 MES 转生产订单联动',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'crm_customer',
    'auth_prefix' => 'crm',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 5) database: CRM 安装所需的 SQL 文件
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 6) 安装说明
$readme = <<<'TXT'
CRM 客户关系管理 - 应用包
============================

安装步骤（二选一）：
方式一：上传安装（推荐）
1. 使用超级管理员登录后台，进入「应用中心」。
2. 点击「上传应用包」，选择本 zip 文件上传。
3. 系统自动解压合并并执行安装，完成后即可使用。

方式二：手动解压
1. 将本 zip 解压到 OA/ThinkMes 项目根目录（与 app、public、database 同级），选择合并覆盖。
2. 进入「应用中心」，在 CRM 卡片上点击「安装」。
3. 安装完成后即可使用（客户、联系人、商机、合同、跟进、回款、产品、销售订单、报表）。

与 MES 联动：
- 若项目中已安装 MES，销售订单可「转生产订单」到 MES，实现从销售到生产的闭环。

TXT;
file_put_contents($outDir . '/CRM安装说明.txt', $readme);

// 7) 创建 ZIP（输出到 dist/ 发布目录）
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
$baseName = 'thinkmes-crm-' . $version;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $path = $file->getRealPath();
        $entry = $baseName . '/' . substr($path, strlen($outDir) + 1);
        $zip->addFile($path, $entry);
    }
}
$zip->close();

echo "打包完成: {$zipPath}\n";
echo "zip 已输出到 dist/。应用中心 -> 上传应用包 -> 选择本 zip 即可安装（文件会合并到项目根目录的正常目录）。\n";
