#!/usr/bin/env php
<?php
/**
 * 支付独立应用打包脚本（与 CRM/AI 一致）
 * 用法：在项目根目录执行  php build/pack_payment.php
 * 生成：build/thinkmes-payment-1.0.zip，上传到「应用中心」安装。
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_payment.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-payment';
$zipPath = $root . '/dist/thinkmes-payment-' . $version . '.zip';

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

copyDirRecursive($root . '/app/admin/controller/payment', $outDir . '/app/admin/controller/payment');
copyDirRecursive($root . '/app/admin/view/payment', $outDir . '/app/admin/view/payment');
@mkdir(dirname($outDir . '/app/admin/route/payment.php'), 0755, true);
copy($root . '/app/admin/route/payment.php', $outDir . '/app/admin/route/payment.php');

copyDirRecursive($root . '/app/common/lib/payment', $outDir . '/app/common/lib/payment');
@mkdir(dirname($outDir . '/app/api/controller/Payment.php'), 0755, true);
copy($root . '/app/api/controller/Payment.php', $outDir . '/app/api/controller/Payment.php');

// 前端 JS（如有）
$paymentJsDir = $root . '/public/assets/js/backend/payment';
if (is_dir($paymentJsDir)) {
    copyDirRecursive($paymentJsDir, $outDir . '/public/assets/js/backend/payment');
}

$sqlFiles = ['migrate_add_payment_tables.sql', 'migrate_add_payment_callback_log.sql', 'seed_payment_menu.sql'];
$appManifest = [
    'key' => 'payment',
    'title' => '支付管理',
    'description' => '支付网关与订单；虎皮椒/易支付/官方支付宝微信；租户订单支付回调',
    'version' => $version,
    'sql_files' => $sqlFiles,
    'check_table' => 'payment_gateway',
    'auth_prefix' => 'payment',
];
file_put_contents($outDir . '/app.json', json_encode($appManifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

@mkdir($outDir . '/database', 0755, true);
foreach ($sqlFiles as $f) {
    $src = $root . '/database/' . $f;
    if (is_file($src)) copy($src, $outDir . '/database/' . $f);
}

$readme = <<<'TXT'
支付管理 - 应用包
==================

安装：应用中心 -> 上传应用包，选择本 zip；或解压到项目根目录合并后，在应用中心点击「安装」。

支持通道：
- 官方支付宝、官方微信（预留，需自行接入 SDK）
- 虎皮椒(讯虎) https://www.xunhupay.com 支付宝/微信
- 易支付(8-pay 等) https://www.8-pay.cn 兼容个人版

使用：
1. 后台「支付管理」->「支付网关」添加通道并填写配置。
2. 异步通知地址填写：https://你的域名/api/payment/notify/网关ID（网关ID为添加后列表中的 ID）。
3. 业务侧调用 API：POST /api/payment/create，参数 gateway_id, order_no, amount, title, notify_url, return_url；返回 pay_url 或 form_html 引导用户支付。
4. 支付成功后自动更新 payment_order 与 tenant_order（若订单号一致）。

TXT;
file_put_contents($outDir . '/支付安装说明.txt', $readme);

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
$baseName = 'thinkmes-payment-' . $version;
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
