<?php
return [
    'addons_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR,
    'hooks'       => [
        'app_init',
        'login_after',
        'upload_after',
        'before_create',
        'after_create',
        'before_update',
        'after_update',
        'before_delete',
        'after_delete',
        'workflow_before_transition',
        'workflow_after_transition',
        'workflow_before_approve',
        'workflow_after_approve',
        'custom_field_before_save',
        'custom_field_after_save',
    ],
    'market'      => [
        'enabled'           => true,
        'market_url'        => 'https://market.keleadmin.com/api',
        'auto_check_update' => true,
        'check_interval'    => 86400,
    ],
];
