<?php
use think\facade\Route;

Route::get('/', 'Index/index');
Route::get('index', 'Index/index');
Route::get('step2', 'Index/step2');
Route::get('step3', 'Index/step3');
Route::get('step4', 'Index/step4');
Route::get('step5', 'Index/step5');
Route::post('step3', 'Index/step3');
Route::post('step4', 'Index/step4');
Route::post('install', 'Index/install');
