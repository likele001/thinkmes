<?php
use think\facade\Route;

// AI 提示词工坊 - 前端页面路由
Route::get('prompt$', 'prompt.Index/index');
Route::get('prompt/index$', 'prompt.Index/index');
Route::get('prompt/template/index$', 'prompt.Template/index');
Route::get('prompt/template/detail$', 'prompt.Template/detail');
Route::get('prompt/generate/index$', 'prompt.Generate/index');
Route::get('prompt/history/index$', 'prompt.History/index');
Route::get('prompt/purchase/index$', 'prompt.Purchase/index');
