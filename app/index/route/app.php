<?php
use think\facade\Route;

Route::get('user/login', 'User/login');
Route::get('user/register', 'User/register');
Route::get('user/logout', 'User/logout');
Route::get('user/index', 'User/index');
Route::get('user/profile', 'User/profile');
Route::get('user/changepwd', 'User/changepwd');
Route::get('user/forgot', 'User/forgot');
Route::get('user/resetpwd', 'User/resetpwd');
Route::get('worker/scan', 'Worker/scan');
Route::get('trace/query', 'Trace/query');
Route::get('trace/detail', 'Trace/detail');
Route::get('trace.html', 'Trace/detail');
