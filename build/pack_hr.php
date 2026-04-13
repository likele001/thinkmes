#!/usr/bin/env php
<?php
/**
 * 人事考勤应用打包脚本（与 CRM/AI 一致）
 * 用法：在项目根目录执行  php build/pack_hr.php [版本号]
 * 生成：dist/thinkmes-hr-{版本}.zip，上传到「应用中心」安装。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_hr.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-hr';
$zipPath = $root . '/dist/thinkmes-hr-' . $version . '.zip';

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

// 1) app/admin: hr 控制器、模型、视图
copyDirRecursive($root . '/app/admin/controller/hr', $outDir . '/app/admin/controller/hr');
copyDirRecursive($root . '/app/admin/model/hr', $outDir . '/app/admin/model/hr');
copyDirRecursive($root . '/app/admin/view/hr', $outDir . '/app/admin/view/hr');

// 2) 独立人事路由
@mkdir(dirname($outDir . '/app/admin/route/hr.php'), 0755, true);
copy($root . '/app/admin/route/hr.php', $outDir . '/app/admin/route/hr.php');

// 3) 前端 JS（如有）
$hrJsDir = $root . '/public/assets/js/backend/hr';
if (is_dir($hrJsDir)) {
    copyDirRecursive($hrJsDir, $outDir . '/public/assets/js/backend/hr');
}

// 4) app.json 应用清单
$sqlFiles = ['migrate_hr.sql', 'seed_hr_menu.sql'];
$appManifest = [
    'key' => 'hr',
    'title' => '人事考勤',
    'description' => '部门、岗位、员工、考勤、请假、加班；与 MES 计件工资联动',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'hr_department',
    'auth_prefix' => 'hr',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 5) database: 人事安装所需的 SQL 文件
@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

// 6) 安装说明
$readme = <<<'TXT'
人事考勤 - 应用包
==================

安装：应用中心 -> 上传应用包，选择本 zip；或解压到项目根目录合并后，在应用中心点击「安装」。

功能：
- 组织架构：部门、岗位管理
- 员工管理：员工档案、入职、离职
- 考勤管理：考勤打卡、考勤统计
- 请假管理：请假申请、审批、统计
- 加班管理：加班申请、审批、统计
- 与 MES 联动：计件工资可关联员工与考勤数据

TXT;
file_put_contents($outDir . '/人事安装说明.txt', $readme);

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
$baseName = 'thinkmes-hr-' . $version;
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
