<?php
/**
 * 示例插件：在安装时加载，用于注册钩子
 * 可用钩子：app_init, login_after, upload_after（见 config/addon.php）
 */
use app\common\lib\Hook;

Hook::listen('login_after', function ($admin) {
    // 示例：登录成功后记录（可写日志、同步第三方等）
    if (is_array($admin) && !empty($admin['username'])) {
        // file_put_contents(runtime_path() . 'demo_login.log', date('Y-m-d H:i:s') . ' ' . $admin['username'] . "\n", FILE_APPEND);
    }
});

Hook::listen('upload_after', function ($result) {
    // 示例：上传成功后可处理（如缩略图、水印等）
    if (is_array($result) && !empty($result['url'])) {
        // ...
    }
});
