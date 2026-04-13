<?php
use think\facade\Route;

// 基础版：仅 C 端用户相关；Worker/Trace/Customer 为 MES 模块，不包含
Route::get('user/login', 'User/login');
Route::get('user/register', 'User/register');
Route::get('user/logout', 'User/logout');
Route::get('user/index', 'User/index');
Route::get('user/profile', 'User/profile');
Route::get('user/changepwd', 'User/changepwd');
Route::get('user/forgot', 'User/forgot');
Route::get('user/resetpwd', 'User/resetpwd');
