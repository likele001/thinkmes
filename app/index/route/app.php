<?php
use think\facade\Route;

// 前端 C 端用户：登录、注册、会员中心
Route::get('user/login', 'User/login');
Route::get('user/register', 'User/register');
Route::get('user/logout', 'User/logout');
Route::get('user/index', 'User/index');
Route::get('user/profile', 'User/profile');
Route::get('user/changepwd', 'User/changepwd');
Route::get('user/forgot', 'User/forgot');
Route::get('user/resetpwd', 'User/resetpwd');
