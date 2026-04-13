#!/usr/bin/env php
<?php
/**
 * 基础框架打包脚本（与 build 一致：复制 app/config/public/vendor，仅 database/init.sql，解压后访问 /install 分步安装）
 * 含：租户、套餐、应用中心、用户管理、打印/短信等底座；不含：MES、CRM、AI、支付、设备、人事、财务。
 * 用法：在项目根目录执行  php build/pack_base.php  [版本号]
 * 生成：build/thinkmes-base-{版本}.zip
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_base.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-base';
$zipPath = $root . '/dist/thinkmes-base-' . $version . '.zip';

echo "项目根目录: {$root}\n";
echo "输出目录: {$outDir}\n";
echo "打包文件: {$zipPath}\n";

if (!is_dir($root . '/vendor')) {
    die("错误：未找到 vendor 目录。请先在项目根目录执行 composer install 再打包。\n");
}

// 清理并创建输出目录
if (is_dir($outDir)) {
    delTree($outDir);
}
mkdir($outDir, 0755, true);

// 需排除的路径（相对于项目根）— 仅排除业务应用，保留租户/套餐/应用中心/用户管理
$excludeDirs = [
    'public/uploads',
    'app/admin/controller/mes',
    'app/admin/model/mes',
    'app/admin/view/mes',
    'app/admin/controller/crm',
    'app/admin/model/crm',
    'app/admin/view/crm',
    'app/admin/controller/ai',
    'app/admin/view/ai',
    'app/admin/controller/payment',
    'app/admin/model/payment',
    'app/admin/view/payment',
    'app/admin/controller/equipment',
    'app/admin/model/equipment',
    'app/admin/view/equipment',
    'app/admin/controller/hr',
    'app/admin/model/hr',
    'app/admin/view/hr',
    'app/admin/controller/finance',
    'app/admin/model/finance',
    'app/admin/view/finance',
    '.git',
    'runtime/cache',
    'runtime/log',
    'runtime/session',
    'runtime/temp',
    'build/output',
];
$excludeFiles = [
    '.env',
    'runtime/install.lock',
    'public/.user.ini',
    'app/admin/route/crm.php',
    'app/admin/route/mes.php',
    'app/admin/route/ai.php',
    'app/admin/route/payment.php',
    'app/admin/route/equipment.php',
    'app/admin/route/hr.php',
    'app/admin/route/finance.php',
    'app/index/controller/Worker.php',
    'app/index/controller/Trace.php',
    'app/index/controller/Customer.php',
    'app/api/controller/Worker.php',
    'app/api/controller/Scanwork.php',
    'app/api/controller/Customer.php',
    'app/api/controller/Cockpit.php',
    'app/api/controller/Ai.php',
    'app/api/controller/Payment.php',
];
// 用基础版覆盖（路由去业务应用 require；Index 用项目自带以保留租户/菜单等）
$replaceWithBase = [
    'app/admin/route/app.php' => $root . '/build/admin_route_base.php',
    'app/index/route/app.php' => $root . '/build/index_route_base.php',
    'app/api/route/app.php'   => $root . '/build/api_route_base.php',
];
// database：只保留 init.sql（安装向导只执行此文件）
$databaseOnly = ['init.sql'];

function delTree($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        $path = $dir . '/' . $f;
        is_dir($path) ? delTree($path) : @unlink($path);
    }
    rmdir($dir);
}

function shouldExclude($relPath, $excludeDirs, $excludeFiles) {
    $relPath = str_replace('\\', '/', $relPath);
    foreach ($excludeDirs as $d) {
        if ($relPath === $d || strpos($relPath, $d . '/') === 0) return true;
    }
    if (in_array($relPath, $excludeFiles, true)) return true;
    return false;
}

function copyDir($src, $dst, $excludeDirs, $excludeFiles, $rootPath) {
    $rootLen = strlen($rootPath);
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (($f = readdir($dir)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $srcPath = $src . '/' . $f;
        $relPath = substr($srcPath, $rootLen + 1);
        $relPath = str_replace('\\', '/', $relPath);
        if (shouldExclude($relPath, $excludeDirs, $excludeFiles)) continue;
        if (is_dir($srcPath)) {
            copyDir($srcPath, $dst . '/' . $f, $excludeDirs, $excludeFiles, $rootPath);
        } else {
            @copy($srcPath, $dst . '/' . $f);
        }
    }
    closedir($dir);
}

// 1) 复制 app, config, public, vendor, addons（扩展如云存储）, extend（若有）, composer.json、LICENSE、README.md 等
$copyRootItems = ['app', 'config', 'public', 'vendor', 'composer.json', 'composer.lock', 'LICENSE', 'README.md'];
if (is_dir($root . '/addons')) $copyRootItems[] = 'addons';
if (is_dir($root . '/extend')) $copyRootItems[] = 'extend';
foreach ($copyRootItems as $item) {
    $src = $root . '/' . $item;
    if (!file_exists($src)) continue;
    $rel = $item;
    if (shouldExclude($rel, $excludeDirs, $excludeFiles)) continue;
    if (is_dir($src)) {
        copyDir($src, $outDir . '/' . $item, $excludeDirs, $excludeFiles, $root);
    } else {
        @copy($src, $outDir . '/' . $item);
    }
}

// 2) database：只复制 init.sql（基础版优先用 build/database/init_base.sql，不改仓库里的 init.sql，完整版不受影响）
mkdir($outDir . '/database', 0755, true);
$initSql = $root . '/build/database/init_base.sql';
if (!is_file($initSql)) {
    $initSql = $root . '/database/init.sql';
}
copy($initSql, $outDir . '/database/init.sql');

// 3) runtime 目录结构（空目录 + .gitkeep）
mkdir($outDir . '/runtime', 0755, true);
foreach (['cache', 'log', 'session', 'temp'] as $sub) {
    mkdir($outDir . '/runtime/' . $sub, 0755, true);
    file_put_contents($outDir . '/runtime/' . $sub . '/.gitkeep', '');
}

// 4) 用基础版覆盖（仅路由；Index 用复制过去的项目自带）
foreach ($replaceWithBase as $targetRel => $baseFile) {
    if (!is_file($baseFile)) {
        echo "警告：缺少 {$baseFile}\n";
        continue;
    }
    $targetPath = $outDir . '/' . $targetRel;
    @mkdir(dirname($targetPath), 0755, true);
    copy($baseFile, $targetPath);
}

// 5) 删除基础版不需要的单个文件（AI 包管理；api 业务控制器）
$removeFiles = [
    'app/admin/controller/AiPackage.php',
    'app/api/controller/Cockpit.php',
    'app/api/controller/Scanwork.php',
    'app/api/controller/Payment.php',
    'app/api/controller/Ai.php',
    'app/api/controller/Customer.php',
    'app/api/controller/Worker.php',
];
foreach ($removeFiles as $f) {
    $path = $outDir . '/' . $f;
    if (is_file($path)) {
        unlink($path);
        echo "已移除: {$f}\n";
    }
}

// 6) .env.example
if (is_file($root . '/config/.env.example')) {
    copy($root . '/config/.env.example', $outDir . '/.env.example');
}

// 7) 安装说明.txt
$installTxt = <<<'TXT'
========================================
  ThinkMES 基础框架 - 安装说明
========================================

本包为「仅底座」版本，不含 MES/CRM/AI 等业务应用；
业务应用可通过安装后的「应用中心 → 上传应用包」安装。

【安装步骤】（与 build 一致，分步安装）

1. 解压本 zip 到服务器目录。

2. 将网站运行目录（Web 根目录）指向解压后的 public 目录。

3. 在项目根目录（与 app、public 同级）执行：composer install

4. 浏览器访问：http://你的域名/install

5. 按安装向导步骤：
   · 步骤一：同意安装协议
   · 步骤二：环境检测
   · 步骤三：填写数据库（主机、端口、库名、用户名、密码、表前缀）
   · 步骤四：设置超级管理员账号与密码
   · 步骤五：确认并执行安装

6. 安装成功后使用页面提示的后台地址登录（随机入口）。

【目录权限】runtime、public/uploads 需可写。

========================================
TXT;
file_put_contents($outDir . '/安装说明.txt', $installTxt);

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
$baseName = 'thinkmes-base-' . $version;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $path = $file->getRealPath();
        $entry = $baseName . '/' . substr($path, strlen($outDir) + 1);
        $zip->addFile($path, $entry);
    }
}
$zip->close();

echo "打包完成: {$zipPath}\n";
echo "zip 已输出到 dist/。用户解压后访问 /install 进行安装，安装完成后仅通过随机入口访问后台。\n";
