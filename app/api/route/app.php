<?php
use think\facade\Route;

Route::get('index/index', 'Index/index');
Route::get('doc', 'Doc/index');
Route::get('doc/index', 'Doc/index');

// C端用户：无需登录
Route::post('user/register', 'User/register');
Route::post('user/login', 'User/login');
Route::post('user/forgot', 'User/forgot');
Route::post('user/resetPassword', 'User/resetPassword');
Route::get('user/captcha', 'User/captcha');
Route::get('user/captchaMode', 'User/captchaMode');

// 小程序登录（无需登录，按租户隔离）
Route::post('miniapp/login', 'Miniapp/login');

// C端用户：需登录
Route::get('user/profile', 'User/profile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/profile', 'User/profile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/updateProfile', 'User/updateProfile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/changePassword', 'User/changePassword')->middleware(\app\api\middleware\UserAuth::class);
Route::get('user/logout', 'User/logout')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/logout', 'User/logout')->middleware(\app\api\middleware\UserAuth::class);

// 小程序绑定（需登录）
Route::post('miniapp/bind', 'Miniapp/bind')->middleware(\app\api\middleware\UserAuth::class);

// C端用户：文件上传（需登录）
Route::post('common/upload', 'Common/upload')->middleware(\app\api\middleware\UserAuth::class);
