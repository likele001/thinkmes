<?php
// 全局中间件定义文件
return [
    // Session 初始化（必须开启，否则登录态无法保存，CheckAuth 会报 session 为 null）
    \think\middleware\SessionInit::class,
    // 多语言：从 cookie(think_lang) / header / 浏览器 解析当前语言，index 与 admin 切换语言后生效
    \think\middleware\LoadLangPack::class,
    // 全局安全过滤器：XSS/SQL注入基础拦截与输入消毒
    \app\common\middleware\Security::class,
];
