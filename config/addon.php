<?php
// +----------------------------------------------------------------------
// | 插件配置
// +----------------------------------------------------------------------
return [
    'addons_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR,
    // 预设钩子
    'hooks'       => ['app_init', 'login_after', 'upload_after'],
];
