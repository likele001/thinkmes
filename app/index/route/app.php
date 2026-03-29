<?php
use think\facade\Route;

// 先注册具体路由，避免被后面的 :lang 单段路由抢先匹配导致 /index/user/index、/index/customer/index 等 404
Route::get('user/login', 'User/login');
Route::get('user/register', 'User/register');
Route::get('user/logout', 'User/logout');
Route::get('user/index', 'User/index');
Route::get('user/profile', 'User/profile');
Route::get('user/changepwd', 'User/changepwd');
Route::get('user/forgot', 'User/forgot');
Route::get('user/resetpwd', 'User/resetpwd');
Route::get('worker/index', 'Worker/index');
Route::get('worker/tasks', 'Worker/tasks');
Route::get('worker/report', 'Worker/report');
Route::get('worker/records', 'Worker/records');
Route::get('worker/wage', 'Worker/wage');
Route::get('worker/scan', 'Worker/scan');
Route::get('trace/query', 'Trace/query');
Route::get('trace/detail', 'Trace/detail');
Route::get('trace.html', 'Trace/detail');

Route::get('customer/login', 'Customer/login');
Route::get('customer/logout', 'Customer/logout');
Route::get('customer/index', 'Customer/index');
Route::get('customer/orders', 'Customer/orders');

// 自媒体工作流（独立应用，用户中心）
Route::get('wemedia/index', 'wemedia.Index/index');
Route::get('wemedia/topic/index', 'wemedia.Topic/index');
Route::get('wemedia/topic/add', 'wemedia.Topic/add');
Route::get('wemedia/topic/edit', 'wemedia.Topic/edit');
Route::get('wemedia/topic/del', 'wemedia.Topic/del');
Route::post('wemedia/topic/save', 'wemedia.Topic/save');
Route::post('wemedia/topic/del', 'wemedia.Topic/del');
Route::get('wemedia/topic/list', 'wemedia.Topic/list');
Route::post('wemedia/topic/generate', 'wemedia.Topic/generate');
Route::get('wemedia/copy/index', 'wemedia.Copy/index');
Route::get('wemedia/copy/add', 'wemedia.Copy/add');
Route::get('wemedia/copy/edit', 'wemedia.Copy/edit');
Route::get('wemedia/copy/list', 'wemedia.Copy/list');
Route::post('wemedia/copy/save', 'wemedia.Copy/save');
Route::post('wemedia/copy/del', 'wemedia.Copy/del');
Route::post('wemedia/copy/generateTitle', 'wemedia.Copy/generateTitle');
Route::post('wemedia/copy/generateContent', 'wemedia.Copy/generateContent');
Route::post('wemedia/copy/generateTags', 'wemedia.Copy/generateTags');
Route::get('wemedia/material/index', 'wemedia.Material/index');
Route::get('wemedia/material/add', 'wemedia.Material/add');
Route::get('wemedia/material/edit', 'wemedia.Material/edit');
Route::get('wemedia/material/list', 'wemedia.Material/list');
Route::post('wemedia/material/save', 'wemedia.Material/save');
Route::post('wemedia/material/del', 'wemedia.Material/del');
Route::post('wemedia/material/upload', 'wemedia.Material/upload');
Route::get('wemedia/video/index', 'wemedia.Video/index');
Route::get('wemedia/video/add', 'wemedia.Video/add');
Route::get('wemedia/video/edit', 'wemedia.Video/edit');
Route::get('wemedia/video/list', 'wemedia.Video/list');
Route::post('wemedia/video/save', 'wemedia.Video/save');
Route::post('wemedia/video/del', 'wemedia.Video/del');
Route::post('wemedia/video/generateScript', 'wemedia.Video/generateScript');
Route::post('wemedia/video/generateFromCopy', 'wemedia.Video/generateFromCopy');
Route::post('wemedia/video/generateAudio', 'wemedia.Video/generateAudio');
Route::post('wemedia/video/generateVideo', 'wemedia.Video/generateVideo');
Route::post('wemedia/video/generateCoverImage', 'wemedia.Video/generateCoverImage');
Route::post('wemedia/video/generateAiVideo', 'wemedia.Video/generateAiVideo');
Route::post('wemedia/video/generateAiVideoFromText', 'wemedia.Video/generateAiVideoFromText');
Route::post('wemedia/video/generateDigitalHuman', 'wemedia.Video/generateDigitalHuman');
Route::get('wemedia/video/videoGenerateTip', 'wemedia.Video/videoGenerateTip');
Route::get('wemedia/schedule/index', 'wemedia.Schedule/index');
Route::get('wemedia/schedule/add', 'wemedia.Schedule/add');
Route::get('wemedia/schedule/edit', 'wemedia.Schedule/edit');
Route::get('wemedia/schedule/list', 'wemedia.Schedule/list');
Route::post('wemedia/schedule/save', 'wemedia.Schedule/save');
Route::post('wemedia/schedule/del', 'wemedia.Schedule/del');
Route::get('wemedia/report/index', 'wemedia.Report/index');
Route::get('wemedia/report/list', 'wemedia.Report/list');
Route::get('wemedia/report/chart', 'wemedia.Report/chart');
Route::post('wemedia/report/save', 'wemedia.Report/save');
Route::post('wemedia/report/del', 'wemedia.Report/del');
Route::get('wemedia/compliance/index', 'wemedia.Compliance/index');
Route::get('wemedia/compliance/list', 'wemedia.Compliance/list');
Route::post('wemedia/compliance/check', 'wemedia.Compliance/check');
Route::post('wemedia/compliance/del', 'wemedia.Compliance/del');

// 语言切换（/index/lang/zh-cn、/index/lang/en-us、/index/lang/ko），302 跳回来源页
Route::get('lang/:lang', function ($lang) {
    $allow = config('lang.allow_lang_list', []);
    $cookieVar = config('lang.cookie_var', 'think_lang');
    if (is_array($allow) && in_array($lang, $allow, true)) {
        cookie($cookieVar, $lang, ['expire' => 86400 * 7, 'path' => '/']);
    }
    $referer = request()->header('referer');
    if ($referer !== '' && $referer !== null && strpos((string) $referer, '/index/lang/') === false) {
        return redirect((string) $referer);
    }
    return redirect(request()->root(true) . '/index/user/login');
})->pattern(['lang' => '[a-z\-]+']);

// 租户自助购买路由（更具体的路由放前面）
Route::get('purchase/form', 'Purchase/form');
Route::get('purchase', 'Purchase/index');
Route::get('register', 'Purchase/register');
