<?php
declare(strict_types=1);
use think\facade\Route;
use app\api\middleware\UserAuth;

// API - AI提示词工坊（公开接口）
Route::group('prompt', function () {
    Route::get('categories',     'prompt.Template/categories');
    Route::get('templates',      'prompt.Template/index');
    Route::get('template/detail','prompt.Template/detail');
    Route::get('category/index', 'prompt.Template/categories');
    Route::get('template/categories', 'prompt.Template/categories');
    Route::get('template/index', 'prompt.Template/index');
    Route::get('template/detail','prompt.Template/detail');
    Route::get('products',       'prompt.Purchase/products');
    Route::post('purchase/notify','prompt.Purchase/notify');
});

// API - 需要登录
Route::group('prompt', function () {
    Route::get('quota',          'prompt.Generate/quota');
    Route::get('generate/quota', 'prompt.Generate/quota');
    Route::post('generate/generateVideo', 'prompt.Generate/generateVideo');
    Route::get('generate/generateVideo', 'prompt.Generate/generateVideo');
    Route::get('generate/queryVideoTask', 'prompt.Generate/queryVideoTask');
    Route::post('generate/generateImage', 'prompt.Generate/generateImage');
    Route::get('generate/generateImage', 'prompt.Generate/generateImage');
    Route::get('generate/queryImageTask', 'prompt.Generate/queryImageTask');
    Route::get('generate/history', 'prompt.Generate/history');
    Route::post('generate/favorite', 'prompt.Generate/favorite');
    Route::post('generate',      'prompt.Generate/run');
    Route::post('generate/run',  'prompt.Generate/run');
    Route::get('history',        'prompt.Generate/history');
    Route::post('favorite',      'prompt.Generate/favorite');
    Route::post('purchase/create','prompt.Purchase/create');
    Route::get('orders',         'prompt.Purchase/orders');
    Route::get('purchase/orders', 'prompt.Purchase/orders');
    Route::get('purchase/orderStatus', 'prompt.Purchase/orderStatus');
    Route::get('purchase/paymentMethods', 'prompt.Purchase/paymentMethods');
})->middleware(UserAuth::class);
