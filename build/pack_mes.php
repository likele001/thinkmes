#!/usr/bin/env php
<?php
/**
 * MES 应用打包脚本：生成可在 OA/基础版项目中安装的 MES 应用包
 * 用法：在项目根目录执行  php build/pack_mes.php
 * 生成：build/thinkmes-mes-1.0.zip，解压到 OA 项目根目录合并后，在后台「应用中心」点击安装 MES。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_mes.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-mes';
$zipPath = $root . '/dist/thinkmes-mes-' . $version . '.zip';

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

// 清理并创建输出目录
if (is_dir($outDir)) {
    delTree($outDir);
}
mkdir($outDir, 0755, true);

// 1) app/admin: mes 控制器、模型、视图 + 独立 MES 路由（合并到 mes.php，不覆盖 app.php）
copyDirRecursive($root . '/app/admin/controller/mes', $outDir . '/app/admin/controller/mes');
copyDirRecursive($root . '/app/admin/model/mes', $outDir . '/app/admin/model/mes');
copyDirRecursive($root . '/app/admin/view/mes', $outDir . '/app/admin/view/mes');
@mkdir(dirname($outDir . '/app/admin/route/mes.php'), 0755, true);
copy($root . '/app/admin/route/mes.php', $outDir . '/app/admin/route/mes.php');

// 2) app/index: Worker、Trace、Customer 控制器 + 完整 index 路由
@mkdir($outDir . '/app/index/controller', 0755, true);
foreach (['Worker.php', 'Trace.php', 'Customer.php'] as $f) {
    $src = $root . '/app/index/controller/' . $f;
    if (is_file($src)) copy($src, $outDir . '/app/index/controller/' . $f);
}
@mkdir($outDir . '/app/index/route', 0755, true);
copy($root . '/app/index/route/app.php', $outDir . '/app/index/route/app.php');

// 3) app/api: Worker、Scanwork、Customer 控制器 + 完整 api 路由
@mkdir($outDir . '/app/api/controller', 0755, true);
foreach (['Worker.php', 'Scanwork.php', 'Customer.php'] as $f) {
    $src = $root . '/app/api/controller/' . $f;
    if (is_file($src)) copy($src, $outDir . '/app/api/controller/' . $f);
}
@mkdir($outDir . '/app/api/route', 0755, true);
copy($root . '/app/api/route/app.php', $outDir . '/app/api/route/app.php');

// 4) app.json 应用清单（应用中心上传安装用）
$sqlFiles = [
    'migrate_add_mes_tables.sql',
    'migrate_add_mes_extended_tables.sql',
    'migrate_add_mes_complete_supply_chain.sql',
    'seed_mes_menu.sql',
];
$appManifest = [
    'key' => 'mes',
    'title' => 'MES 制造执行系统',
    'description' => '订单、产品、BOM、工序、报工、生产计划、库存、质检、工资、发货、看板；与 CRM 销售订单联动',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'mes_product',
    'auth_prefix' => 'mes',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 5) database: MES 安装所需的 SQL 文件
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 6) public/assets/js/backend: MES 前端
$mesJsDir = $root . '/public/assets/js/backend/mes';
if (is_dir($mesJsDir)) {
    copyDirRecursive($mesJsDir, $outDir . '/public/assets/js/backend/mes');
}
$mesJs = $root . '/public/assets/js/backend/mes.js';
if (is_file($mesJs)) {
    @mkdir($outDir . '/public/assets/js/backend', 0755, true);
    copy($mesJs, $outDir . '/public/assets/js/backend/mes.js');
}

// 7) 安装说明
$readme = <<<'TXT'
MES 制造执行系统 - 应用包
============================

安装步骤（二选一）：
方式一：上传安装（推荐）
1. 使用超级管理员登录后台，进入「应用中心」。
2. 点击「上传应用包」，选择本 zip 文件上传。
3. 系统自动解压合并并执行安装，完成后即可使用。

方式二：手动解压
1. 将本 zip 解压到 OA/ThinkMes 项目根目录（与 app、public、database 同级），选择合并覆盖。
2. 进入「应用中心」，在 MES 卡片上点击「安装」。
3. 安装完成后即可使用（订单、产品、BOM、工序、报工、生产计划、库存、质检等）。

注意：本包会覆盖以下路由文件，请确认您的项目为 ThinkMes 基础版或兼容结构。
- app/admin/route/mes.php（独立追加，不覆盖 app.php）
- app/index/route/app.php
- app/api/route/app.php

TXT;
file_put_contents($outDir . '/MES安装说明.txt', $readme);

// 8) 创建 ZIP（输出到 dist/ 发布目录）
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
$baseName = 'thinkmes-mes-' . $version;
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
