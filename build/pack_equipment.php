#!/usr/bin/env php
<?php
/**
 * 设备管理应用打包脚本（与 CRM/AI 一致）
 * 用法：在项目根目录执行  php build/pack_equipment.php [版本号]
 * 生成：dist/thinkmes-equipment-{版本}.zip，上传到「应用中心」安装。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_equipment.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-equipment';
$zipPath = $root . '/dist/thinkmes-equipment-' . $version . '.zip';

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

// 1) app/admin: equipment 控制器、模型、视图
copyDirRecursive($root . '/app/admin/controller/equipment', $outDir . '/app/admin/controller/equipment');
copyDirRecursive($root . '/app/admin/model/equipment', $outDir . '/app/admin/model/equipment');
copyDirRecursive($root . '/app/admin/view/equipment', $outDir . '/app/admin/view/equipment');

// 2) 独立设备路由
@mkdir(dirname($outDir . '/app/admin/route/equipment.php'), 0755, true);
copy($root . '/app/admin/route/equipment.php', $outDir . '/app/admin/route/equipment.php');

// 3) 前端 JS（如有）
$equipmentJsDir = $root . '/public/assets/js/backend/equipment';
if (is_dir($equipmentJsDir)) {
    copyDirRecursive($equipmentJsDir, $outDir . '/public/assets/js/backend/equipment');
}

// 4) app.json 应用清单
$sqlFiles = ['migrate_equipment.sql', 'seed_equipment_menu.sql'];
$appManifest = [
    'key' => 'equipment',
    'title' => '设备管理',
    'description' => '设备档案、保养计划、点检、维修、运行记录；设备状态统计',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'equipment',
    'auth_prefix' => 'equipment',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 5) database: 设备安装所需的 SQL 文件
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 6) 安装说明
$readme = <<<'TXT'
设备管理 - 应用包
==================

安装：应用中心 -> 上传应用包，选择本 zip；或解压到项目根目录合并后，在应用中心点击「安装」。

功能：
- 设备档案：设备编码、名称、型号、状态、位置等
- 保养计划：定期保养计划与执行记录
- 设备点检：点检项目、标准、记录
- 设备维修：维修申请、维修记录
- 运行记录：设备运行时长、故障统计
- 设备统计：设备状态、利用率统计

TXT;
file_put_contents($outDir . '/设备安装说明.txt', $readme);

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
$baseName = 'thinkmes-equipment-' . $version;
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
