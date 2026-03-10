<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

use think\facade\Config;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// Health check / status endpoint for load balancers and container probes
Route::get('status', 'index/health/status');



Route::get('lang/:lang', function ($lang) {
    $allowLangList = Config::get('lang.allow_lang_list');
    if (in_array($lang, $allowLangList)) {
        cookie(Config::get('lang.cookie_name'), $lang, Config::get('lang.cookie_expire'));
    }
    // 强制跳转到首页，而不是返回上一页
    return redirect('/');
});