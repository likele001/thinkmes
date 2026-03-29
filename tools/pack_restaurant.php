<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$targetBase = $root . '/dist';
@mkdir($targetBase, 0755, true);
$buildDir = $targetBase . '/restaurant_build';
$zipPath = $targetBase . '/restaurant.zip';

$delTree = function (string $dir) use (&$delTree): void {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        $p = $dir . '/' . $f;
        if (is_dir($p)) $delTree($p);
        else @unlink($p);
    }
    @rmdir($dir);
};

$copyDir = function (string $src, string $dst) use (&$copyDir): void {
    if (!is_dir($src)) return;
    @mkdir($dst, 0755, true);
    $files = array_diff(scandir($src), ['.', '..']);
    foreach ($files as $f) {
        $sp = $src . '/' . $f;
        $dp = $dst . '/' . $f;
        if (is_dir($sp)) $copyDir($sp, $dp);
        else {
            @mkdir(dirname($dp), 0755, true);
            @copy($sp, $dp);
        }
    }
};

if (is_dir($buildDir)) {
    $delTree($buildDir);
}
@unlink($zipPath);

$pkgRoot = $buildDir . '/restaurant';
@mkdir($pkgRoot, 0755, true);

$manifest = [
    'key' => 'restaurant',
    'title' => '餐饮SaaS',
    'description' => '门店/桌台/菜品/扫码点餐',
    'auth_prefix' => 'restaurant',
    'sql_files' => [
        'restaurant/create_tables.sql',
        'restaurant/seed_restaurant_menu.sql',
    ],
    'tables' => [
        'restaurant_store',
        'restaurant_area',
        'restaurant_table',
        'restaurant_category',
        'restaurant_item',
        'restaurant_item_option_group',
        'restaurant_item_option',
        'restaurant_combo',
        'restaurant_combo_item',
        'restaurant_cart',
        'restaurant_order',
        'restaurant_order_item',
        'restaurant_kds_event',
    ],
];
file_put_contents($pkgRoot . '/app.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$paths = [
    'app/admin/controller/restaurant',
    'app/admin/model/restaurant',
    'app/admin/route/restaurant.php',
    'app/admin/view/restaurant',
    'app/api/controller/restaurant',
    'app/api/route/restaurant.php',
    'app/api/route/restaurant_openclaw.php',
    'public/assets/js/backend/restaurant',
    'public/restaurant',
    'restaurant_miniapp',
    'database/restaurant',
    'app/common/lib/restaurant',
    'app/command',
    'openclaw_server',
];

foreach ($paths as $p) {
    $src = $root . '/' . $p;
    $dst = $pkgRoot . '/' . $p;
    if (is_dir($src)) {
        $copyDir($src, $dst);
    } elseif (is_file($src)) {
        @mkdir(dirname($dst), 0755, true);
        @copy($src, $dst);
    }
}

$zip = new ZipArchive();
if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    fwrite(STDERR, "cannot create zip\n");
    exit(1);
}
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($buildDir, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $file) {
    $filePath = $file->getPathname();
    $relPath = substr($filePath, strlen($buildDir) + 1);
    $zip->addFile($filePath, $relPath);
}
$zip->close();

echo $zipPath . PHP_EOL;
