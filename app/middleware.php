<?php
// 全局中间件定义文件
return [
    // Session 初始化（必须开启，否则登录态无法保存，CheckAuth 会报 session 为 null）
    \think\middleware\SessionInit::class,
];
