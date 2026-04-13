#!/usr/bin/env php
<?php
/**
 * 财务模块应用打包脚本（与 CRM/AI 一致）
 * 用法：在项目根目录执行  php build/pack_finance.php [版本号]
 * 生成：dist/thinkmes-finance-{版本}.zip，上传到「应用中心」安装。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_finance.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-finance';
$zipPath = $root . '/dist/thinkmes-finance-' . $version . '.zip';

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

// 1) app/admin: finance 控制器、模型、视图
copyDirRecursive($root . '/app/admin/controller/finance', $outDir . '/app/admin/controller/finance');
copyDirRecursive($root . '/app/admin/model/finance', $outDir . '/app/admin/model/finance');
copyDirRecursive($root . '/app/admin/view/finance', $outDir . '/app/admin/view/finance');

// 2) 独立财务路由
@mkdir(dirname($outDir . '/app/admin/route/finance.php'), 0755, true);
copy($root . '/app/admin/route/finance.php', $outDir . '/app/admin/route/finance.php');

// 3) 前端 JS（如有）
$financeJsDir = $root . '/public/assets/js/backend/finance';
if (is_dir($financeJsDir)) {
    copyDirRecursive($financeJsDir, $outDir . '/public/assets/js/backend/finance');
}

// 4) app.json 应用清单
$sqlFiles = ['migrate_finance.sql', 'seed_finance_menu.sql'];
$appManifest = [
    'key' => 'finance',
    'title' => '财务管理',
    'description' => '应收账款、应付账款、收款、付款、利润统计；与 CRM 订单/回款、采购/供应商闭环',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'finance_receivable',
    'auth_prefix' => 'finance',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 5) database: 财务安装所需的 SQL 文件
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 6) 安装说明
$readme = <<<'TXT'
财务管理 - 应用包
==================

安装：应用中心 -> 上传应用包，选择本 zip；或解压到项目根目录合并后，在应用中心点击「安装」。

功能：
- 应收账款：可关联 CRM 客户、MES 订单或 CRM 销售订单
- 应付账款：可关联采购订单、供应商
- 收款管理：应收账款收款记录
- 付款管理：应付账款付款记录
- 利润统计：收入、支出、利润报表
- 与 CRM/MES 联动：订单回款、采购付款闭环

TXT;
file_put_contents($outDir . '/财务安装说明.txt', $readme);

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
$baseName = 'thinkmes-finance-' . $version;
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
