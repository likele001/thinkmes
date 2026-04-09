<?php
declare(strict_types=1);
use think\facade\Route;

// 后台 - AI提示词工坊路由
Route::group('prompt', function () {
    Route::get('category/index',  'prompt.Category/index');
    Route::post('category/index', 'prompt.Category/index');
    Route::get('category/add',    'prompt.Category/add');
    Route::post('category/add',   'prompt.Category/add');
    Route::get('category/edit',   'prompt.Category/edit');
    Route::post('category/edit',  'prompt.Category/edit');
    Route::post('category/del',   'prompt.Category/del');

    Route::get('template/index',  'prompt.Template/index');
    Route::post('template/index', 'prompt.Template/index');
    Route::get('template/add',    'prompt.Template/add');
    Route::post('template/add',   'prompt.Template/add');
    Route::get('template/edit',   'prompt.Template/edit');
    Route::post('template/edit',  'prompt.Template/edit');
    Route::post('template/del',   'prompt.Template/del');

    Route::get('generation/index',  'prompt.Generation/index');
    Route::post('generation/index', 'prompt.Generation/index');
    Route::post('generation/del',   'prompt.Generation/del');

    Route::get('quota/index',    'prompt.Quota/index');
    Route::post('quota/index',   'prompt.Quota/index');
    Route::post('quota/adjust',  'prompt.Quota/adjust');

    Route::get('ai_config/index',  'prompt.AiConfig/index');
    Route::post('ai_config/index', 'prompt.AiConfig/index');
    Route::get('ai_config/add',    'prompt.AiConfig/add');
    Route::post('ai_config/add',   'prompt.AiConfig/add');
    Route::get('ai_config/edit',   'prompt.AiConfig/edit');
    Route::post('ai_config/edit',  'prompt.AiConfig/edit');
    Route::post('ai_config/del',   'prompt.AiConfig/del');
    Route::post('ai_config/test',  'prompt.AiConfig/test');

    Route::get('config/index',   'prompt.Config/index');
    Route::post('config/index',  'prompt.Config/index');
})->middleware(\app\admin\middleware\CheckAuth::class);
