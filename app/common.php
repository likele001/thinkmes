<?php
// 应用公共文件

use think\facade\Lang;

if (!function_exists('__')) {
    /**
     * 多语言翻译函数
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return string
     */
    function __(string $name, array $vars = [], string $lang = ''): string
    {
        return Lang::get($name, $vars, $lang);
    }
}
