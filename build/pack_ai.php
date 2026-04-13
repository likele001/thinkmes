#!/usr/bin/env php
<?php
/**
 * 工厂 AI 应用打包脚本（与 CRM 一致：独立应用包，上传安装）
 * 用法：在项目根目录执行  php build/pack_ai.php
 * 生成：build/thinkmes-ai-1.0.zip，解压合并后可在「应用中心」安装工厂 AI。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_ai.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-ai';
$zipPath = $root . '/dist/thinkmes-ai-' . $version . '.zip';

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

if (is_dir($outDir)) delTree($outDir);
mkdir($outDir, 0755, true);

// 1) app/admin: AI 控制器、视图
copyDirRecursive($root . '/app/admin/controller/ai', $outDir . '/app/admin/controller/ai');
copyDirRecursive($root . '/app/admin/view/ai', $outDir . '/app/admin/view/ai');

// 2) 独立 AI 路由
@mkdir(dirname($outDir . '/app/admin/route/ai.php'), 0755, true);
copy($root . '/app/admin/route/ai.php', $outDir . '/app/admin/route/ai.php');

// 3) 公共类库（AI 依赖）
@mkdir(dirname($outDir . '/app/common/lib/AiService.php'), 0755, true);
copy($root . '/app/common/lib/AiService.php', $outDir . '/app/common/lib/AiService.php');

// 4) 前端 JS
copyDirRecursive($root . '/public/assets/js/backend/ai', $outDir . '/public/assets/js/backend/ai');

// 5) app.json 应用清单（应用中心上传安装用，需 key、sql_files；title/description/auth_prefix 用于展示）
$sqlFiles = ['migrate_add_ai_tables.sql', 'migrate_add_ai_package.sql', 'migrate_add_ai_module_switch.sql', 'seed_ai_menu.sql'];
$appManifest = [
    'key' => 'ai',
    'title' => '工厂 AI',
    'description' => '语音报工、异常检测、智能问答、生产日报、CRM 智能跟单；支持平台/租户全局开关',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'ai_config',
    'auth_prefix' => 'ai',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 6) database: 全部 AI 安装用 SQL
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 7) 安装说明
$readme = <<<'TXT'
工厂 AI - 应用包
================

安装步骤（二选一）：
方式一：上传安装（推荐）
1. 使用超级管理员登录后台，进入「应用中心」。
2. 点击「上传应用包」，选择本 zip 文件上传。
3. 系统自动解压合并并执行安装，完成后即可使用。

方式二：手动解压
1. 将本 zip 解压到项目根目录（与 app、public、database 同级），选择合并覆盖。
2. 进入「应用中心」，在「工厂 AI」卡片上点击「安装」。
3. 安装完成后，在「工厂 AI」下可配置全局开关与四个子功能开关：报工、异常检测、智能问答、CRM 自动跟单。

功能说明：
- 语音报工、报工异常检测、智能问答、AI 生产日报、CRM 智能跟单。
- 平台/租户可在「AI 套餐管理」->「全局开关」中单独开启或关闭：报工、异常检测、智能问答、CRM 自动跟单。

TXT;
file_put_contents($outDir . '/AI安装说明.txt', $readme);

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
$baseName = 'thinkmes-ai-' . $version;
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
