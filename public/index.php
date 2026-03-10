<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\App;

// [ 应用入口文件 ]

require __DIR__ . '/../vendor/autoload.php';

$envFile = __DIR__ . '/../.env';
// 未安装时可能没有 .env，写一份最小配置避免框架报错（安装时会覆盖）
if (!is_file($envFile)) {
    $minimal = "APP_DEBUG = false\n";
    @file_put_contents($envFile, $minimal);
}
$adminEntry = '';
if (is_file($envFile)) {
    $envContent = @file_get_contents($envFile);
    if ($envContent !== false && preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $envContent, $m) && trim($m[1]) !== '') {
        $adminEntry = trim($m[1]);
        // 兼容旧配置：若为 xxx.php 则按路径式入口处理，实际访问 /xxx/... 即可
        if (substr($adminEntry, -4) === '.php') {
            $adminEntry = substr($adminEntry, 0, -4);
        }
    }
}

// 当前请求路径（用于下面判断是否为安装页 / 后台入口）
$path = '';
if (isset($_GET['s']) && (string) $_GET['s'] !== '') {
    $path = '/' . ltrim((string) $_GET['s'], '/');
}
if (($path === '' || $path === '/') && isset($_SERVER['PATH_INFO']) && (string) $_SERVER['PATH_INFO'] !== '') {
    $path = '/' . ltrim((string) $_SERVER['PATH_INFO'], '/');
}
if (($path === '' || $path === '/') && isset($_SERVER['REQUEST_URI'])) {
    $q = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    if ($q !== null && $q !== '' && preg_match('/\bs=([^&]+)/', $q, $m)) {
        $path = '/' . ltrim(rawurldecode($m[1]), '/');
    }
}
if ($path === '' || $path === '/') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = '/' . ltrim((string) $path, '/');
}
// 安装页始终放行，不参与后台入口判断
$isInstall = ($path === '/install' || $path === '/install/' || strpos($path, '/install/') === 0);

// 路径式后台入口：/随机串/xxx 经伪静态到 index.php?s=/随机串/xxx，在此转成 /admin/xxx（不依赖单独 .php 文件）
if (!$isInstall && $adminEntry !== '' && !defined('ADMIN_ENTRY_REQUEST')) {
    $prefix = '/' . $adminEntry;
    if ($path === $prefix || $path === $prefix . '/' || strpos($path, $prefix . '/') === 0) {
        define('ADMIN_ENTRY_REQUEST', true);
        $after = $path === $prefix || $path === $prefix . '/' ? '' : substr($path, strlen($prefix) + 1);
        $after = $after === '' ? '/index/index' : '/' . ltrim($after, '/');
        $qs = [];
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
            parse_str($_SERVER['QUERY_STRING'], $qs);
            unset($qs['s']);
        }
        if (isset($_GET['s'])) {
            unset($_GET['s']);
        }
        $_SERVER['REQUEST_URI'] = '/admin' . $after . ($qs !== [] ? '?' . http_build_query($qs) : '');
        $_SERVER['PATH_INFO'] = '/admin' . $after;
    }
}

// 若已配置随机后台入口，则禁止通过 /admin 直接访问
if (!defined('ADMIN_ENTRY_REQUEST')) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $uri = trim($uri, '/');
    $first = (strpos($uri, '/') !== false) ? substr($uri, 0, strpos($uri, '/')) : $uri;
    if ($first === 'admin' && $adminEntry !== '') {
        http_response_code(404);
        exit('404 Not Found');
    }
}

// 执行HTTP应用并响应
$app = new App();
$http = $app->http;

// 补偿：当请求为 /index/xxx 且 MultiApp 未正确切换时，入口预先绑定 index 应用并加载 app/index/route（路由只在应用下 app/index/route/app.php）
if (!$isInstall && strpos($path, '/index/') === 0) {
    $_GET['s'] = substr($path, 7);
    $indexAppPath = $app->getBasePath() . 'index' . DIRECTORY_SEPARATOR;
    $http->name('index');
    $app->setAppPath($indexAppPath);
    $app->setNamespace('app\\index');
    $http->setRoutePath($indexAppPath . 'route' . DIRECTORY_SEPARATOR);
}

$response = $http->run();

$response->send();

$http->end($response);
