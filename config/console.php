<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    'commands' => [
        'crud'  => \app\command\Crud::class,
        'addon' => \app\command\Addon::class,
        'cache:clear' => \app\command\Clear::class,
    ],
];
