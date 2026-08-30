#!/usr/bin/env php
<?php
/**
 * 工作流应用打包：审批引擎（定义/模块接入/审批中心/实例）+ common 服务 + 路由 + 前端 JS
 * 用法：在项目根目录执行  php build/pack_workflow.php [版本]
 * 生成：dist/thinkmes-workflow-{版本}.zip，应用中心上传安装。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_workflow.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-workflow';
$zipPath = $root . '/dist/thinkmes-workflow-' . $version . '.zip';

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
    @rmdir($dir);
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

// 1) 后台工作流
copyDirRecursive($root . '/app/admin/controller/workflow', $outDir . '/app/admin/controller/workflow');
copyDirRecursive($root . '/app/admin/model/workflow', $outDir . '/app/admin/model/workflow');
copyDirRecursive($root . '/app/admin/view/workflow', $outDir . '/app/admin/view/workflow');
@mkdir(dirname($outDir . '/app/admin/route/workflow.php'), 0755, true);
copy($root . '/app/admin/route/workflow.php', $outDir . '/app/admin/route/workflow.php');

// 2) 公共审批引擎
copyDirRecursive($root . '/app/common/service/workflow', $outDir . '/app/common/service/workflow');

// 3) 前端
$jsDir = $root . '/public/assets/js/backend/workflow';
if (is_dir($jsDir)) {
    copyDirRecursive($jsDir, $outDir . '/public/assets/js/backend/workflow');
}

$sqlFiles = [
    'migrate_wf_engine_linear.sql',
    'seed_workflow_app_menu.sql',
];
$appManifest = [
    'key' => 'workflow',
    'title' => '工作流审批',
    'description' => '审批流定义、业务模块接入、审批中心、流程实例；线性节点审批引擎',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'wf_instance',
    'auth_prefix' => 'admin/workflow',
    'tables' => [
        'fa_wf_definition',
        'fa_wf_node',
        'fa_wf_module',
        'fa_wf_instance',
        'fa_wf_task',
        'fa_wf_log',
    ],
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) {
        copy($src, $outDir . '/database/' . $f);
    } else {
        echo "警告：缺少 database/{$f}\n";
    }
}

$readme = <<<'TXT'
工作流审批 - 应用包
============================

安装（推荐）：
1. 超级管理员 → 应用中心 → 上传应用包 → 选择本 zip。
2. 系统自动合并 app、public、database 并执行 SQL（建表 + 菜单权限）。

说明：
- 与基础包中已集成的「工作流」为同一套代码；本包供未带工作流的基础镜像单独安装，或用于覆盖升级。
- 随机后台入口下，视图与 JS 应使用 Config.moduleurl，勿写死 /admin/。

卸载：
- 应用中心可卸载；可选勾选删除数据表将移除 fa_wf_* 六张表（请谨慎）。

TXT;
file_put_contents($outDir . '/工作流应用安装说明.txt', $readme);

@mkdir(dirname($zipPath), 0755, true);
if (file_exists($zipPath)) {
    unlink($zipPath);
}
$zip = new ZipArchive();
if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    die("无法创建 ZIP: {$zipPath}\n");
}
$baseName = 'thinkmes-workflow-' . $version;
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($outDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($files as $file) {
    if (!$file->isDir()) {
        $path = $file->getRealPath();
        $entry = $baseName . '/' . substr($path, strlen($outDir) + 1);
        $zip->addFile($path, $entry);
    }
}
$zip->close();

echo "打包完成: {$zipPath}\n";
echo "zip 已输出到 dist/。应用中心 -> 上传应用包 安装。\n";
